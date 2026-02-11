<?php
declare(strict_types=1);
namespace OCA\fairmeeting\AppInfo;

use OCA\fairmeeting\Config\Config;
use OCA\fairmeeting\Listener\CalendarEventListener;
use OCA\fairmeeting\Search\Provider;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\AppFramework\Http\ContentSecurityPolicy;
use OCA\DAV\Events\CalendarObjectCreatedEvent;
use OCA\DAV\Events\CalendarObjectUpdatedEvent;

class Application extends App implements IBootstrap {
	public const APP_ID = 'fairmeeting';
	public const APP_NAME = 'fairmeeting videoconferencing';

	public function __construct() {
		parent::__construct(self::APP_ID);

		$policy = new ContentSecurityPolicy();
		$policy->addAllowedFrameDomain('https://fairmeeting.net');
		$policy->addAllowedFrameDomain('https://www.paypal.com');
		$policy->addAllowedFrameDomain('https://payment-links.mollie.com');
		$policy->addAllowedFrameDomain('https://www.mollie.com');
		$policy->addAllowedFrameDomain('https://token.pro.fairmeeting.net');

		$cspManager = \OC::$server->get(\OCP\Security\IContentSecurityPolicyManager::class);
		$cspManager->addDefaultPolicy($policy);
	}

	public function register(IRegistrationContext $context): void {
		require_once __DIR__ . '/../../vendor/autoload.php';
		$context->registerSearchProvider(Provider::class);
		
		// Register calendar event listeners for automatic fairmeeting integration
		$context->registerEventListener(CalendarObjectCreatedEvent::class, CalendarEventListener::class);
		$context->registerEventListener(CalendarObjectUpdatedEvent::class, CalendarEventListener::class);
	}

	public function boot(IBootContext $context): void {
		$this->setUpfairmeetingServerUrl($context);
		$this->setUpHelpLink($context);
	}

	private function setUpfairmeetingServerUrl(IBootContext $context): void {
		/** @var Config $config */
		$config = $context->getAppContainer()->query(Config::class);

		$serverUrl = $config->fairmeetingServerUrl();

		if (empty($serverUrl)) {
			$config->updatefairmeetingServerUrl('https://fairmeeting.net/');
			return;
		}

		if (substr($serverUrl, -1) !== '/') {
			$config->updatefairmeetingServerUrl($serverUrl . '/');
		}
	}

	private function setUpHelpLink(IBootContext $context): void {
		/** @var Config $config */
		$config = $context->getAppContainer()->query(Config::class);

		$helpLink = $config->helpLink();

		if (empty($helpLink)) {
			$config->updateHelpLink('https://git.fairkom.net/hosting/fairmeeting/-/wikis/home');
		}
	}
}
