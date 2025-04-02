<?php

declare(strict_types=1);

namespace OCA\fairmeeting\Settings;

use OCP\IURLGenerator;
use OCP\Settings\IIconSection;

class AdminSection implements IIconSection {
	/** @var IURLGenerator */
	private $urlgen;

	public function __construct(IURLGenerator $urlgen) {
		$this->urlgen = $urlgen;
	}

	public function getIcon(): string {
		return $this->urlgen->imagePath('fairmeeting', 'settings.svg');
	}

	public function getID(): string {
		return 'fairmeeting';
	}

	public function getName(): string {
		return 'fairmeeting';
	}

	public function getPriority(): int {
		return 80;
	}
}
