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
 * @method ?bool getSkipPrejoin()
 * @method void setSkipPrejoin(?bool $skipPrejoin)
 * @method ?bool getDisableDeepLinking()
 * @method void setDisableDeepLinking(?bool $disableDeepLinking)
 */
class Room extends Entity implements JsonSerializable {
	/** @var string */
	protected $name;

	/** @var string */
	protected $creatorId;

	/** @var string */
	protected $publicId;

	/** @var bool */
	protected $allStartAudioMuted = false;

	/** @var bool */
	protected $allStartVideoMuted = false;

	/** @var ?bool Null means: use admin default. */
	protected $skipPrejoin = null;

	/** @var ?bool Null means: use admin default. */
	protected $disableDeepLinking = null;

	public function __construct() {
		$this->addType('id', 'integer');
		$this->addType('allStartAudioMuted', 'boolean');
		$this->addType('allStartVideoMuted', 'boolean');
		$this->addType('skipPrejoin', 'boolean');
		$this->addType('disableDeepLinking', 'boolean');
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
			'skipPrejoin' => $this->getSkipPrejoin(),
			'disableDeepLinking' => $this->getDisableDeepLinking(),
		];
	}
}
