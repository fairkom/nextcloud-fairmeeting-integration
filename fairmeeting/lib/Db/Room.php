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
 * @method string getSource()
 * @method void setSource(string $source)
 * @method ?\DateTime getCreatedAt()
 * @method void setCreatedAt(?\DateTime $createdAt)
 * @method ?\DateTime getLastJoinedAt()
 * @method void setLastJoinedAt(?\DateTime $lastJoinedAt)
 */
class Room extends Entity implements JsonSerializable {
	public const SOURCE_MANUAL = 'manual';
	public const SOURCE_CALENDAR = 'calendar';

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

	/** @var string Either 'manual' (created via fairmeeting UI) or 'calendar' (auto-created by the listener). */
	protected $source = self::SOURCE_MANUAL;

	/** @var ?\DateTime */
	protected $createdAt = null;

	/** @var ?\DateTime */
	protected $lastJoinedAt = null;

	public function __construct() {
		$this->addType('id', 'integer');
		$this->addType('allStartAudioMuted', 'boolean');
		$this->addType('allStartVideoMuted', 'boolean');
		$this->addType('skipPrejoin', 'boolean');
		$this->addType('disableDeepLinking', 'boolean');
		$this->addType('createdAt', 'datetime');
		$this->addType('lastJoinedAt', 'datetime');
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
			'source' => $this->getSource(),
			'createdAt' => $this->getCreatedAt() ? $this->getCreatedAt()->format(\DateTimeInterface::ATOM) : null,
			'lastJoinedAt' => $this->getLastJoinedAt() ? $this->getLastJoinedAt()->format(\DateTimeInterface::ATOM) : null,
		];
	}
}
