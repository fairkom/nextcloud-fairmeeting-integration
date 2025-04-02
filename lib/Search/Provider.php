<?php

declare(strict_types=1);

namespace OCA\fairmeeting\Search;

use OCA\fairmeeting\AppInfo\Application;
use OCA\fairmeeting\Db\Room;
use OCA\fairmeeting\Db\RoomMapper;
use OCA\Theming\ThemingDefaults;
use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\IUser;
use OCP\IUserSession;
use OCP\Search\IProvider;
use OCP\Search\ISearchQuery;
use OCP\Search\SearchResult;
use OCP\Search\SearchResultEntry;

use function array_map;
use function strpos;
use function substr;

class Provider implements IProvider {
	/**
	 * @var IL10N
	 */
	private $l;

	/**
	 * @var IURLGenerator
	 */
	private $urlGenerator;

	/**
	 * @var IUserSession
	 */
	private $userSession;

	/**
	 * @var RoomMapper
	 */
	private $roomMapper;

	/**
	 * @var ThemingDefaults
	 */
	private $themingDefaults;

	public function __construct(
		IL10N $l,
		IURLGenerator $urlGenerator,
		IUserSession $userSession,
		RoomMapper $roomMapper,
		ThemingDefaults $themingDefaults
	) {
		$this->l = $l;
		$this->urlGenerator = $urlGenerator;
		$this->userSession = $userSession;
		$this->roomMapper = $roomMapper;
		$this->themingDefaults = $themingDefaults;
	}

	public function getId(): string {
		return Application::APP_ID;
	}

	public function getName(): string {
		return $this->l->t('conferences');
	}

	/**
	 * @param string $route
	 * @param array<mixed> $routeParameters
	 * @return int
	 */
	public function getOrder(
		string $route,
		array $routeParameters
	): int {
		if (strpos($route, Application::APP_ID . '.') === 0) {
			// Active app, prefer my results
			return -1;
		}

		return 50;
	}

	public function search(IUser $user, ISearchQuery $query): SearchResult {
		$rooms = $this->retrieveRooms($query);
		$iconUrl = $this->urlGenerator->getAbsoluteURL(
			'/index.php/svg/fairmeeting/app?color=' . substr(
				$this->themingDefaults->getColorPrimary(),
				1
			)
		);

		$roomResults = array_map(
			function (Room $room) use ($iconUrl): SearchResultEntry {
				return new SearchResultEntry(
					$iconUrl,
					$room->getName(),
					'asd',
					$this->urlGenerator->linkToRoute(
						Application::APP_ID . '.page.room',
						[
							'publicId' => $room->getPublicId(),
							'roomName' => $room->getName(),
						]
					)
				);
			},
			$rooms
		);

		return SearchResult::complete(
			$this->getName(),
			$roomResults
		);
	}

	/**
	 * @param ISearchQuery $query
	 * @return array<Room>
	 */
	private function retrieveRooms(ISearchQuery $query): array {
		$user = $this->userSession->getUser();

		if ($user === null) {
			return [];
		}

		return $this->roomMapper->findAllByCreatorAndName($user, $query->getTerm());
	}
}
