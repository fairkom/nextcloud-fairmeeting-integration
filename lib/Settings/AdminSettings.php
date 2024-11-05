<?php

declare(strict_types=1);

namespace OCA\fairmeeting\Settings;

use OCP\AppFramework\Http\TemplateResponse;
use OCP\Settings\ISettings;

class AdminSettings implements ISettings {
	public function getForm() {
		return new TemplateResponse('fairmeeting', 'admin', []);
	}

	public function getSection() {
		return 'fairmeeting';
	}

	public function getPriority() {
		return 50;
	}
}
