<?php

declare(strict_types=1);

namespace OCA\fairmeeting\Controller;

use OCA\fairmeeting\AppInfo\Application;
use OCA\fairmeeting\Config\Config;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\IConfig;
use OCP\IRequest;

/**
 * Reads and writes the app config rows backing the admin settings page.
 *
 * The page used to talk to the legacy OCP.AppConfig JS API directly. That
 * API handed its success callback an XML document up to NC32 and a parsed
 * object from NC33 on, which broke the page on NC33. Going through our own
 * endpoints keeps the shape stable across NC versions and collapses the
 * ~20 requests per page load / save into one.
 *
 * Both methods require an admin session: the app framework demands admin
 * unless a controller method is annotated NoAdminRequired.
 */
class AdminController extends Controller {
	/**
	 * Settings the admin page may read and write, with the defaults the
	 * page falls back to. Keys outside this list are rejected.
	 *
	 * Values are stored and returned as strings — the form binds
	 * checkboxes to '1' / '0'.
	 *
	 * @var array<string, string>
	 */
	private const SETTINGS = [
		Config::KEY_fairmeeting_SERVER_URL => '',
		Config::KEY_HELP_LINK => '',
		Config::KEY_JWT_SECRET => '',
		Config::KEY_JWT_APP_ID => '',
		Config::KEY_JWT_AUDIENCE => '',
		Config::KEY_JWT_ISSUER => '',
		Config::KEY_JWT_TOKEN_SERVICE_URL => '',
		Config::KEY_PRO_SERVER_URL => '',
		Config::KEY_PRO_GROUP_NAME => 'fairmeeting',
		Config::KEY_PRO_SERVER_LABEL => 'pro',
		Config::KEY_ROOM_NAME_PREFIX => '',
		Config::KEY_OPEN_IN_NEW_TAB => '1',
		Config::KEY_DISPLAY_JOIN_USING_THE_fairmeeting_APP => '1',
		Config::KEY_ALL_SHARING_INVITES => '1',
		Config::KEY_CALENDAR_INTEGRATION_ENABLED => '0',
		Config::KEY_CALENDAR_MINIMUM_DURATION => '15',
		Config::KEY_CALENDAR_USE_KEYWORD => '0',
		Config::KEY_CALENDAR_KEYWORD => '#fm',
		Config::KEY_CALENDAR_KEYWORD_REPLACE_LOCATION => '1',
		Config::KEY_CALENDAR_KEYWORD_REPLACE_DESCRIPTION => '0',
		Config::KEY_MEETING_SKIP_PREJOIN => '0',
		Config::KEY_MEETING_DISABLE_DEEP_LINKING => '0',
	];

	private IConfig $config;

	public function __construct(
		string $AppName,
		IRequest $request,
		IConfig $config
	) {
		parent::__construct($AppName, $request);
		$this->config = $config;
	}

	public function index(): DataResponse {
		$settings = [];

		foreach (self::SETTINGS as $key => $default) {
			$value = $this->config->getAppValue(Application::APP_ID, $key, '');
			$settings[$key] = $value === '' ? $default : $value;
		}

		return new DataResponse($settings);
	}

	/**
	 * @param array<string, mixed> $settings
	 */
	public function update(array $settings): DataResponse {
		foreach ($settings as $key => $value) {
			if (!array_key_exists($key, self::SETTINGS)) {
				continue;
			}
			$this->config->setAppValue(
				Application::APP_ID,
				$key,
				$value === null ? '' : (string)$value
			);
		}

		return $this->index();
	}
}
