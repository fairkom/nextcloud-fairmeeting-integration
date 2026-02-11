<?php
declare(strict_types=1);

namespace OCA\fairmeeting\Listener;

use OCA\fairmeeting\Config\Config;
use OCA\DAV\Events\CalendarObjectCreatedEvent;
use OCA\DAV\Events\CalendarObjectUpdatedEvent;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\ILogger;
use OCP\Calendar\IManager as ICalendarManager;
use Sabre\VObject\Component\VCalendar;
use Sabre\VObject\Reader;
use OCA\DAV\CalDAV\CalDavBackend;

/**
 * @template-implements IEventListener<CalendarObjectCreatedEvent|CalendarObjectUpdatedEvent>
 */
class CalendarEventListener implements IEventListener {
	private Config $config;
	private ILogger $logger;
	private ICalendarManager $calendarManager;
	private CalDavBackend $calDavBackend;

	public function __construct(
		Config $config,
		ILogger $logger,
		ICalendarManager $calendarManager,
		CalDavBackend $calDavBackend
	) {
		$this->config = $config;
		$this->logger = $logger;
		$this->calendarManager = $calendarManager;
		$this->calDavBackend = $calDavBackend;
	}

	public function handle(Event $event): void {
		$this->logger->info('fairmeeting Calendar Event Listener triggered', [
			'app' => 'fairmeeting',
			'event_type' => get_class($event)
		]);

		if (!$this->config->isCalendarIntegrationEnabled()) {
			$this->logger->info('fairmeeting Calendar Integration is disabled, skipping', [
				'app' => 'fairmeeting'
			]);
			return;
		}

		// Process both CalendarObjectCreatedEvent and CalendarObjectUpdatedEvent
		if ($event instanceof CalendarObjectCreatedEvent || $event instanceof CalendarObjectUpdatedEvent) {
			$this->logger->info('Processing calendar event for fairmeeting integration', [
				'app' => 'fairmeeting',
				'event_type' => get_class($event)
			]);
			$this->scheduleCalendarEventUpdate($event);
		}
	}

	private function scheduleCalendarEventUpdate($event): void {
		// Schedule the update to run after the current request completes
		$calendarData = $event->getCalendarData();
		$objectData = $event->getObjectData();
		
		$this->logger->info('Scheduling delayed fairmeeting update for calendar event', [
			'app' => 'fairmeeting',
			'calendar_uri' => $calendarData['uri'],
			'object_uri' => $objectData['uri']
		]);

		// Store event data for later processing
		$eventData = [
			'calendar_data' => $calendarData,
			'object_data' => $objectData,
			'calendar_object' => $objectData['calendardata']
		];

		// Register shutdown function to process after response is sent
		register_shutdown_function(function() use ($eventData) {
			// Wait a moment for the initial request to fully complete
			sleep(3);
			$this->processStoredCalendarEvent($eventData);
		});
	}

	private function processStoredCalendarEvent(array $eventData): void {
		try {
			$calendarData = $eventData['calendar_data'];
			$objectData = $eventData['object_data'];

			$this->logger->info('fairmeeting processing stored calendar event data', [
				'app' => 'fairmeeting',
				'calendar_uri' => $calendarData['uri'] ?? 'unknown',
				'object_uri' => $objectData['uri'] ?? 'unknown'
			]);

			// Parse the calendar object
			$vCalendar = Reader::read($eventData['calendar_object']);
			
			if (!$vCalendar instanceof VCalendar) {
				return;
			}

			$modified = false;
			
			// Process all VEVENT components
			foreach ($vCalendar->getComponents('VEVENT') as $vEvent) {
				$shouldAdd = $this->shouldAddFairmeeting($vEvent);
				$this->logger->info('Checking if fairmeeting should be added to event', [
					'app' => 'fairmeeting',
					'should_add' => $shouldAdd,
					'event_summary' => isset($vEvent->SUMMARY) ? (string)$vEvent->SUMMARY : 'No summary'
				]);
				
				if ($shouldAdd) {
					$this->addFairmeetingToEvent($vEvent, $calendarData['principaluri']);
					$modified = true;
				}
			}

			// Save the modified calendar object back if we made changes
			if ($modified) {
				$this->logger->info('Attempting to save modified calendar object', [
					'app' => 'fairmeeting',
					'modified' => true
				]);
				$this->saveModifiedStoredCalendarObject($eventData, $vCalendar->serialize());
			} else {
				$this->logger->info('No modifications needed for calendar object', [
					'app' => 'fairmeeting',
					'modified' => false
				]);
			}

		} catch (\Exception $e) {
			$this->logger->error('Error processing calendar event for fairmeeting integration: ' . $e->getMessage(), [
				'app' => 'fairmeeting',
				'exception' => $e
			]);
		}
	}

	private function shouldAddFairmeeting($vEvent): bool {
		// Check if event already has a fairmeeting link
		if (isset($vEvent->LOCATION)) {
			$location = (string)$vEvent->LOCATION;
			if (strpos($location, 'fairmeeting.net') !== false || strpos($location, $this->config->fairmeetingServerUrl()) !== false) {
				return false;
			}
		}

		// Check if event already has a fairmeeting URL in description
		if (isset($vEvent->DESCRIPTION)) {
			$description = (string)$vEvent->DESCRIPTION;
			if (strpos($description, 'fairmeeting.net') !== false || strpos($description, $this->config->fairmeetingServerUrl()) !== false) {
				return false;
			}
		}

		// If keyword mode is enabled, check for keyword in location, title, or description
		if ($this->config->isCalendarUseKeywordEnabled()) {
			$keyword = $this->config->getCalendarKeyword();
			return $this->eventContainsKeyword($vEvent, $keyword);
		}

		// Default behavior: Only add to events that are meetings (have attendees or are longer than configured minimum) AND only if location is empty
		$hasAttendees = isset($vEvent->ATTENDEE) && count($vEvent->ATTENDEE) > 0;
		$minimumDuration = $this->config->getCalendarMinimumDuration(); // in minutes
		
		if ($hasAttendees || $this->isEventLongEnough($vEvent, $minimumDuration)) {
			if (isset($vEvent->LOCATION)) {
				$currentLocation = trim((string)$vEvent->LOCATION);
				return empty($currentLocation);
			}

			return true;
		}

		return false;
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

		if (isset($vEvent->LOCATION)) {
			$location = (string)$vEvent->LOCATION;
			if (stripos($location, $keyword) !== false) {
				return true;
			}
		}

		if (isset($vEvent->DESCRIPTION)) {
			$description = (string)$vEvent->DESCRIPTION;
			if (stripos($description, $keyword) !== false) {
				return true;
			}
		}

		return false;
	}

	private function addFairmeetingToEvent($vEvent, string $principalUri): void {
		// Generate a unique room name based on event
		$eventTitle = isset($vEvent->SUMMARY) ? (string)$vEvent->SUMMARY : 'Meeting';
		$eventUid = isset($vEvent->UID) ? (string)$vEvent->UID : uniqid();
		
		$roomName = $this->generateRoomName($eventTitle, $eventUid);
		$fairmeetingUrl = $this->generateFairmeetingUrl($roomName);

		$locationAdded = false;
		$descriptionAdded = false;
		
		if ($this->config->isCalendarUseKeywordEnabled()) {
			$keyword = $this->config->getCalendarKeyword();
			
			if ($this->config->isCalendarKeywordReplaceLocationEnabled()) {
				if (isset($vEvent->LOCATION)) {
					$currentLocation = (string)$vEvent->LOCATION;
					if (stripos($currentLocation, $keyword) !== false) {
						$vEvent->LOCATION = str_ireplace($keyword, $fairmeetingUrl, $currentLocation);
						$locationAdded = true;
					}
				}
			}
			
			if ($this->config->isCalendarKeywordReplaceDescriptionEnabled()) {
				if (isset($vEvent->DESCRIPTION)) {
					$currentDescription = (string)$vEvent->DESCRIPTION;
					if (stripos($currentDescription, $keyword) !== false) {
						$vEvent->DESCRIPTION = str_ireplace($keyword, $fairmeetingUrl, $currentDescription);
						$descriptionAdded = true;
					}
				}
			}
		} else {
			if (isset($vEvent->LOCATION)) {
				$currentLocation = trim((string)$vEvent->LOCATION);
				if (empty($currentLocation)) {
					$vEvent->LOCATION = $fairmeetingUrl;
					$locationAdded = true;
				}
			} else {

				$vEvent->add('LOCATION', $fairmeetingUrl);
				$locationAdded = true;
			}
		}

		$this->logger->info('Added fairmeeting link to calendar event', [
			'app' => 'fairmeeting',
			'room' => $roomName,
			'url' => $fairmeetingUrl,
			'location_added' => $locationAdded,
			'description_added' => $descriptionAdded,
			'keyword_mode' => $this->config->isCalendarUseKeywordEnabled(),
			'replace_location_enabled' => $this->config->isCalendarKeywordReplaceLocationEnabled(),
			'replace_description_enabled' => $this->config->isCalendarKeywordReplaceDescriptionEnabled()
		]);
	}

	private function generateRoomName(string $eventTitle, string $eventUid): string {
		// Clean the event title for use in URL
		$cleanTitle = preg_replace('/[^a-zA-Z0-9-_]/', '', str_replace(' ', '-', $eventTitle));
		$cleanTitle = strtolower(substr($cleanTitle, 0, 30));
		
		// Add a short hash of the UID to ensure uniqueness
		$hash = substr(md5($eventUid), 0, 8);
		
		$roomName = $cleanTitle . '-' . $hash;
		
		// Add room name prefix if configured
		$prefix = $this->config->getRoomNamePrefix();
		if (!empty($prefix)) {
			$roomName = $prefix . $roomName;
		}
		
		return $roomName;
	}

	private function generateFairmeetingUrl(string $roomName): string {
		$serverUrl = rtrim($this->config->fairmeetingServerUrl(), '/');
		return $serverUrl . '/' . $roomName;
	}

	private function saveModifiedStoredCalendarObject(array $eventData, string $modifiedCalendarData): void {
		// Get calendar info from stored data
		$calendarData = $eventData['calendar_data'];
		$objectData = $eventData['object_data'];

		$this->logger->info('Starting calendar object update via CalDAV Backend (stored)', [
			'app' => 'fairmeeting',
			'calendar_id' => $calendarData['id'],
			'object_uri' => $objectData['uri']
		]);

		try {
			// Use CalDAV Backend directly - the proper low-level API
			$result = $this->calDavBackend->updateCalendarObject(
				$calendarData['id'],
				$objectData['uri'],
				$modifiedCalendarData
			);
			
			$this->logger->info('Successfully updated calendar object with fairmeeting link via CalDAV Backend (stored)', [
				'app' => 'fairmeeting',
				'calendar_id' => $calendarData['id'],
				'object_uri' => $objectData['uri'],
				'result' => $result
			]);
			
		} catch (\Exception $e) {
			$this->logger->error('Failed to update calendar object via CalDAV Backend (stored): ' . $e->getMessage(), [
				'app' => 'fairmeeting',
				'calendar_id' => $calendarData['id'],
				'object_uri' => $objectData['uri'],
				'exception' => $e,
				'exception_class' => get_class($e)
			]);
		}
	}
}