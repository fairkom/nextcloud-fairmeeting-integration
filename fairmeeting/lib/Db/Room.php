<?php

declare(strict_types=1);

namespace OCA\fairmeeting\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

/**
 * @method string getName()
 * @method void setName(string $name)
 * @method string getCreatorId()
 * @method void setCreatorId(string $creatorId)
 * @method string getPublicId()
 * @method void setPublicId(string $publicId)
 * @method bool getAllStartAudioMuted()
 * @method void setAllStartAudioMuted(bool $allStartAudioMuted)
 * @method bool getAllStartVideoMuted()
 * @method void setAllStartVideoMuted(bool $allStartVideoMuted)
 */
class Room extends Entity implements JsonSerializable {
	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var string
	 */
	protected $creatorId;

	/**
	 * @var string
	 */
	protected $publicId;

	/**
	 * @var bool
	 */
	protected $allStartAudioMuted = false;

	/**
	 * @var bool
	 */
	protected $allStartVideoMuted = false;

	public function __construct() {
		$this->addType('id', 'integer');
		$this->addType('allStartAudioMuted', 'boolean');
		$this->addType('allStartVideoMuted', 'boolean');
	}

	/**
	 * @return array<string, mixed>
	 */
	public function jsonSerialize(): array {
		return [
			'id' => $this->getId(),
			'name' => $this->getName(),
			'publicId' => $this->getPublicId(),
			'creatorId' => $this->getCreatorId(),
			'allStartAudioMuted' => $this->getAllStartAudioMuted(),
			'allStartVideoMuted' => $this->getAllStartVideoMuted(),
		];
	}
}
