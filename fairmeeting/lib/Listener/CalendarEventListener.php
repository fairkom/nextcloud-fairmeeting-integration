<?php
declare(strict_types=1);

namespace OCA\fairmeeting\Listener;

use OCA\fairmeeting\Config\Config;
use OCA\DAV\CalDAV\CalDavBackend;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use Psr\Log\LoggerInterface;
use Sabre\VObject\Component\VCalendar;
use Sabre\VObject\Reader;

/**
 * Handles calendar object create/update events from both the modern
 * OCP\Calendar\Events namespace (NC32+) and the legacy OCA\DAV\Events
 * namespace (pre-NC32).
 */
class CalendarEventListener implements IEventListener {
	private Config $config;
	private LoggerInterface $logger;
	private CalDavBackend $calDavBackend;

	public function __construct(
		Config $config,
		LoggerInterface $logger,
		CalDavBackend $calDavBackend
	) {
		$this->config = $config;
		$this->logger = $logger;
		$this->calDavBackend = $calDavBackend;
	}

	public function handle(Event $event): void {
		try {
			if (!$this->config->isCalendarIntegrationEnabled()) {
				return;
			}

			// Accept both the OCP\Calendar\Events variant (NC32+) and the legacy
			// OCA\DAV\Events variant (pre-NC32). Both expose getCalendarData()
			// and getObjectData(), so we duck-type on those methods.
			if (!method_exists($event, 'getCalendarData') || !method_exists($event, 'getObjectData')) {
				return;
			}

			$this->processCalendarEventUpdate($event);
		} catch (\Throwable $e) {
			$this->logger->error('Unhandled error in fairmeeting calendar listener: ' . $e->getMessage(), [
				'app' => 'fairmeeting',
				'exception' => $e,
			]);
		}
	}

	/**
	 * Process the calendar event inline. Earlier versions deferred this to a
	 * register_shutdown_function with sleep(3), which on Apache/PHP-FPM blocked
	 * the response for 3+ seconds per save. The inline write triggers an
	 * UpdatedEvent for our own write, but shouldAddFairmeeting() returns false
	 * on the second pass (URL is already in LOCATION), so there is no loop.
	 */
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
				$this->addFairmeetingToEvent($vEvent);
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
		// Skip if event already contains a fairmeeting link in LOCATION or DESCRIPTION.
		$serverUrl = $this->config->fairmeetingServerUrl();
		foreach (['LOCATION', 'DESCRIPTION'] as $field) {
			if (isset($vEvent->$field)) {
				$value = (string)$vEvent->$field;
				if (strpos($value, 'fairmeeting.net') !== false || strpos($value, $serverUrl) !== false) {
					return false;
				}
			}
		}

		// Keyword mode: only trigger on explicit keyword in LOCATION/DESCRIPTION.
		if ($this->config->isCalendarUseKeywordEnabled()) {
			return $this->eventContainsKeyword($vEvent, $this->config->getCalendarKeyword());
		}

		// Default mode: events with attendees OR longer than minimum duration get a link,
		// but only if LOCATION is empty (so we don't overwrite a user-set room/address).
		$hasAttendees = isset($vEvent->ATTENDEE) && count($vEvent->ATTENDEE) > 0;
		$longEnough = $this->isEventLongEnough($vEvent, $this->config->getCalendarMinimumDuration());

		if (!$hasAttendees && !$longEnough) {
			return false;
		}

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

	private function addFairmeetingToEvent($vEvent): void {
		$eventTitle = isset($vEvent->SUMMARY) ? (string)$vEvent->SUMMARY : 'Meeting';
		$eventUid = isset($vEvent->UID) ? (string)$vEvent->UID : uniqid();

		$roomName = $this->generateRoomName($eventTitle, $eventUid);
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

		// Default mode: fill LOCATION when empty/missing.
		if (isset($vEvent->LOCATION)) {
			if (trim((string)$vEvent->LOCATION) === '') {
				$vEvent->LOCATION = $fairmeetingUrl;
			}
		} else {
			$vEvent->add('LOCATION', $fairmeetingUrl);
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
		$url = rtrim($this->config->fairmeetingServerUrl(), '/') . '/' . $roomName;

		$params = [];
		if ($this->config->isMeetingSkipPrejoinEnabled()) {
			// Pass both the legacy key (Jitsi < 7906) and the modern nested key.
			$params[] = 'config.prejoinPageEnabled=false';
			$params[] = 'config.prejoinConfig.enabled=false';
		}
		if ($this->config->isMeetingDisableDeepLinkingEnabled()) {
			$params[] = 'config.disableDeepLinking=true';
		}
		if ($params) {
			$url .= '#' . implode('&', $params);
		}

		return $url;
	}
}
