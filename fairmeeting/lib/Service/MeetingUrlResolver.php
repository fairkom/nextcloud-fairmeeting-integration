<?php

declare(strict_types=1);

namespace OCA\fairmeeting\Service;

use OCA\fairmeeting\Config\Config;
use OCP\IGroupManager;

/**
 * Resolves the Jitsi server URL per creator. Members of the configured
 * pro group host on the pro server, everyone else on the default. Check
 * runs on every call so group changes apply immediately.
 */
class MeetingUrlResolver {
	private Config $appConfig;
	private IGroupManager $groupManager;

	public function __construct(Config $appConfig, IGroupManager $groupManager) {
		$this->appConfig = $appConfig;
		$this->groupManager = $groupManager;
	}

	public function resolveFor(?string $creatorId): string {
		$default = (string)($this->appConfig->fairmeetingServerUrl() ?? 'https://fairmeeting.net/');
		$proUrl = $this->appConfig->proServerUrl();
		$groupName = $this->appConfig->proGroupName();
		if (empty($proUrl) || empty($groupName) || $creatorId === null || $creatorId === '') {
			return $default;
		}
		return $this->groupManager->isInGroup($creatorId, $groupName)
			? $proUrl
			: $default;
	}
}
