<?php
declare(strict_types=1);

namespace OCA\fairmeeting\Controller;

use OCA\fairmeeting\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\IConfig;
use OCP\IRequest;
use OCP\IUserSession;

class PersonalController extends Controller {
	private IConfig $config;
	private IUserSession $userSession;

	public function __construct(
		string $AppName,
		IRequest $request,
		IConfig $config,
		IUserSession $userSession
	) {
		parent::__construct($AppName, $request);
		$this->config = $config;
		$this->userSession = $userSession;
	}

	/**
	 * @NoAdminRequired
	 */
	public function updateJwtToken(string $jwtToken): DataResponse {
		$user = $this->userSession->getUser();
		if ($user === null) {
			return new DataResponse(['error' => 'unauthenticated'], Http::STATUS_UNAUTHORIZED);
		}
		$this->config->setUserValue(
			$user->getUID(),
			Application::APP_ID,
			'jwt_token',
			$jwtToken
		);
		return new DataResponse(['ok' => true]);
	}
}
