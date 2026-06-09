<?php

declare(strict_types=1);

namespace OCA\fairmeeting\Settings;

use OCA\fairmeeting\Config\Config;
use OCA\fairmeeting\AppInfo\Application;
use OCA\fairmeeting\Service\MeetingUrlResolver;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IConfig;
use OCP\IGroupManager;
use OCP\IUserSession;
use OCP\Settings\ISettings;

class PersonalSettings implements ISettings {
	private Config $appConfig;
	private IConfig $config;
	private IUserSession $userSession;
	private MeetingUrlResolver $urlResolver;
	private IGroupManager $groupManager;

	public function __construct(
		Config $appConfig,
		IConfig $config,
		IUserSession $userSession,
		MeetingUrlResolver $urlResolver,
		IGroupManager $groupManager
	) {
		$this->appConfig = $appConfig;
		$this->config = $config;
		$this->userSession = $userSession;
		$this->urlResolver = $urlResolver;
		$this->groupManager = $groupManager;
	}

	public function getForm() {
		$user = $this->userSession->getUser();
		$uid = $user !== null ? $user->getUID() : '';
		$groupName = $this->appConfig->proGroupName();
		$proUrl = (string)($this->appConfig->proServerUrl() ?? '');
		return new TemplateResponse('fairmeeting', 'personal', [
			'jwt_token_service_url' => $this->appConfig->jwtTokenServiceUrl() ?? '',
			'personal_jwt_token' => $uid === ''
				? ''
				: $this->config->getUserValue($uid, Application::APP_ID, 'jwt_token', ''),
			'effective_server_url' => $this->urlResolver->resolveFor($uid !== '' ? $uid : null),
			'pro_server_url' => $proUrl,
			'pro_group_name' => $groupName,
			'in_pro_group' => $uid !== '' && $proUrl !== '' && $groupName !== ''
				&& $this->groupManager->isInGroup($uid, $groupName),
		]);
	}

	public function getSection() {
		return 'fairmeeting';
	}

	public function getPriority() {
		return 50;
	}
}
