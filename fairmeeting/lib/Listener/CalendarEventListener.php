<?php
declare(strict_types=1);

namespace OCA\fairmeeting\Listener;

use OCA\fairmeeting\Config\Config;
use OCA\fairmeeting\Db\Room;
use OCA\fairmeeting\Db\RoomMapper;
use OCA\DAV\CalDAV\CalDavBackend;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\IURLGenerator;
use Psr\Log\LoggerInterface;
use Sabre\VObject\Component\VCalendar;
use Sabre\VObject\Reader;

class CalendarEventListener implements IEventListener {
	private Config $config;
	private LoggerInterface $logger;
	private CalDavBackend $calDavBackend;
	private IURLGenerator $urlGenerator;
	private RoomMapper $roomMapper;

	public function __construct(
		Config $config,
		LoggerInterface $logger,
		CalDavBackend $calDavBackend,
		IURLGenerator $urlGenerator,
		RoomMapper $roomMapper
	) {
		$this->config = $config;
		$this->logger = $logger;
		$this->calDavBackend = $calDavBackend;
		$this->urlGenerator = $urlGenerator;
		$this->roomMapper = $roomMapper;
	}

	public function handle(Event $event): void {
		try {
			if (!$this->config->isCalendarIntegrationEnabled()) {
				return;
			}

			// Duck-type across NC32 (OCP\Calendar\Events\*) and pre-NC32 (OCA\DAV\Events\*).
			if (!method_exists($event, 'getCalendarData') || !method_exists($event, 'getObjectData')) {
				return;
			}

			$class = get_class($event);
			if (strpos($class, 'Deleted') !== false || strpos($class, 'Trash') !== false) {
				$this->handleCalendarObjectDeleted($event);
			} else {
				$this->processCalendarEventUpdate($event);
			}
		} catch (\Throwable $e) {
			$this->logger->error('Unhandled error in fairmeeting calendar listener: ' . $e->getMessage(), [
				'app' => 'fairmeeting',
				'exception' => $e,
			]);
		}
	}

	private function handleCalendarObjectDeleted($event): void {
		$objectData = $event->getObjectData();
		if (empty($objectData['calendardata'])) {
			return;
		}
		$vCalendar = Reader::read($objectData['calendardata']);
		if (!$vCalendar instanceof VCalendar) {
			return;
		}
		$this->deleteCalendarRoomForEvent($vCalendar);
	}

	private function processCalendarEventUpdate($event): void {
		$calendarData = $event->getCalendarData();
		$objectData = $event->getObjectData();

		$this->logger->debug('fairmeeting processing calendar event', [
			'app' => 'fairmeeting',
			'event_type' => get_class($event),
			'calendar_uri' => $calendarData['uri'] ?? 'unknown',
			'object_uri' => $objectData['uri'] ?? 'unknown',
		]);

		$vCalendar = Reader::read($objectData['calendardata']);
		if (!$vCalendar instanceof VCalendar) {
			return;
		}

		$modified = false;
		foreach ($vCalendar->getComponents('VEVENT') as $vEvent) {
			if ($this->shouldAddFairmeeting($vEvent)) {
				$this->addFairmeetingToEvent($vEvent, $calendarData['principaluri'] ?? '');
				$modified = true;
			}
		}

		if (!$modified) {
			return;
		}

		$this->calDavBackend->updateCalendarObject(
			$calendarData['id'],
			$objectData['uri'],
			$vCalendar->serialize()
		);

		$this->logger->info('fairmeeting link added to calendar event', [
			'app' => 'fairmeeting',
			'calendar_id' => $calendarData['id'],
			'object_uri' => $objectData['uri'],
		]);
	}

	private function shouldAddFairmeeting($vEvent): bool {
		// Skip events that already have a fairmeeting link (direct Jitsi URL
		// from pre-0.23 entries, or our NC join-redirect route).
		$serverUrl = $this->config->fairmeetingServerUrl();
		$ncJoinPath = '/apps/fairmeeting/j/';
		foreach (['LOCATION', 'DESCRIPTION'] as $field) {
			if (isset($vEvent->$field)) {
				$value = (string)$vEvent->$field;
				if (strpos($value, 'fairmeeting.net') !== false
					|| strpos($value, $serverUrl) !== false
					|| strpos($value, $ncJoinPath) !== false) {
					return false;
				}
			}
		}

		if ($this->config->isCalendarUseKeywordEnabled()) {
			return $this->eventContainsKeyword($vEvent, $this->config->getCalendarKeyword());
		}

		$hasAttendees = isset($vEvent->ATTENDEE) && count($vEvent->ATTENDEE) > 0;
		$longEnough = $this->isEventLongEnough($vEvent, $this->config->getCalendarMinimumDuration());

		if (!$hasAttendees && !$longEnough) {
			return false;
		}

		// Never overwrite an existing user-set LOCATION.
		if (isset($vEvent->LOCATION)) {
			return trim((string)$vEvent->LOCATION) === '';
		}

		return true;
	}

	private function isEventLongEnough($vEvent, int $minimumMinutes): bool {
		if (!isset($vEvent->DTSTART) || !isset($vEvent->DTEND)) {
			return false;
		}

		$start = $vEvent->DTSTART->getDateTime();
		$end = $vEvent->DTEND->getDateTime();
		$durationMinutes = ($end->getTimestamp() - $start->getTimestamp()) / 60;

		return $durationMinutes >= $minimumMinutes;
	}

	private function eventContainsKeyword($vEvent, string $keyword): bool {
		foreach (['LOCATION', 'DESCRIPTION'] as $field) {
			if (isset($vEvent->$field) && stripos((string)$vEvent->$field, $keyword) !== false) {
				return true;
			}
		}
		return false;
	}

	private function addFairmeetingToEvent($vEvent, string $principalUri): void {
		$eventTitle = isset($vEvent->SUMMARY) ? (string)$vEvent->SUMMARY : 'Meeting';
		$eventUid = isset($vEvent->UID) ? (string)$vEvent->UID : uniqid();

		$roomName = $this->generateRoomName($eventTitle, $eventUid);

		// /j/<publicId> requires a stored Room to mint a JWT.
		$this->ensureRoomExists($roomName, $principalUri);

		$fairmeetingUrl = $this->generateFairmeetingUrl($roomName);

		if ($this->config->isCalendarUseKeywordEnabled()) {
			$keyword = $this->config->getCalendarKeyword();

			if ($this->config->isCalendarKeywordReplaceLocationEnabled() && isset($vEvent->LOCATION)) {
				$current = (string)$vEvent->LOCATION;
				if (stripos($current, $keyword) !== false) {
					$vEvent->LOCATION = str_ireplace($keyword, $fairmeetingUrl, $current);
				}
			}

			if ($this->config->isCalendarKeywordReplaceDescriptionEnabled() && isset($vEvent->DESCRIPTION)) {
				$current = (string)$vEvent->DESCRIPTION;
				if (stripos($current, $keyword) !== false) {
					$vEvent->DESCRIPTION = str_ireplace($keyword, $fairmeetingUrl, $current);
				}
			}
			return;
		}

		if (isset($vEvent->LOCATION)) {
			if (trim((string)$vEvent->LOCATION) === '') {
				$vEvent->LOCATION = $fairmeetingUrl;
			}
		} else {
			$vEvent->add('LOCATION', $fairmeetingUrl);
		}
	}

	private function ensureRoomExists(string $roomName, string $principalUri): void {
		if ($this->roomMapper->findOneByPublicId($roomName) !== null) {
			return;
		}

		// principaluri = "principals/users/<uid>"; fall back to a sentinel for system calendars.
		$creatorId = '_calendar';
		if (preg_match('#^principals/users/([^/]+)$#', $principalUri, $m) === 1) {
			$creatorId = substr($m[1], 0, 64);
		}

		$room = new Room();
		$room->setName($roomName);
		$room->setPublicId($roomName);
		$room->setCreatorId($creatorId);
		$room->setSource(Room::SOURCE_CALENDAR);
		$room->setCreatedAt(new \DateTime());
		$this->roomMapper->insert($room);
	}

	private function deleteCalendarRoomForEvent($vCalendar): void {
		foreach ($vCalendar->getComponents('VEVENT') as $vEvent) {
			$eventTitle = isset($vEvent->SUMMARY) ? (string)$vEvent->SUMMARY : 'Meeting';
			$eventUid = isset($vEvent->UID) ? (string)$vEvent->UID : '';
			if ($eventUid === '') {
				continue;
			}
			$roomName = $this->generateRoomName($eventTitle, $eventUid);
			$room = $this->roomMapper->findOneByPublicId($roomName);
			if ($room !== null && $room->getSource() === Room::SOURCE_CALENDAR) {
				$this->roomMapper->delete($room);
			}
		}
	}

	private function generateRoomName(string $eventTitle, string $eventUid): string {
		$cleanTitle = preg_replace('/[^a-zA-Z0-9-_]/', '', str_replace(' ', '-', $eventTitle));
		$cleanTitle = strtolower(substr($cleanTitle, 0, 30));

		$hash = substr(md5($eventUid), 0, 8);
		$roomName = $cleanTitle . '-' . $hash;

		$prefix = $this->config->getRoomNamePrefix();
		if (!empty($prefix)) {
			$roomName = $prefix . $roomName;
		}

		return $roomName;
	}

	private function generateFairmeetingUrl(string $roomName): string {
		// Built manually because the route table isn't loaded in CalDAV listener context
		// — linkToRoute would swallow RouteNotFoundException and return the bare base URL.
		return rtrim($this->urlGenerator->getAbsoluteURL('/'), '/')
			. '/index.php/apps/fairmeeting/j/' . rawurlencode($roomName);
	}
}
