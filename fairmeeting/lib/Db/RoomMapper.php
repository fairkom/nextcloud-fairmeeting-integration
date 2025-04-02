<?php

declare(strict_types=1);

namespace OCA\fairmeeting\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;
use OCP\IUser;

use function reset;

/**
 * @extends QBMapper<Room>
 */
class RoomMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'fairmeeting_rooms', Room::class);
	}

	/**
	 * @return array<Room>
	 */
	public function findAll(): array {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName())
			->orderBy('name', 'asc');

		return $this->findEntities($qb);
	}

	/**
	 * @param IUser $user
	 * @return array<Room>
	 */
	public function findAllByCreator(IUser $user): array {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName())
			->where(
				$qb->expr()->eq('creator_id', $qb->createNamedParameter($user->getUID()))
			)
			->orderBy('name', 'asc');

		return $this->findEntities($qb);
	}

	/**
	 * @param IUser $user
	 * @param string $name
	 * @return array<Room>
	 */
	public function findAllByCreatorAndName(IUser $user, string $name): array {
		$qb = $this->db->getQueryBuilder();

		$userParam = $qb->createNamedParameter($user->getUID());
		$sanitisedName = str_replace('%', '', $name);
		$nameParam = $qb->createNamedParameter('%' . $sanitisedName . '%');

		$qb->select('*')
			->from($this->getTableName())
			->where(
				$qb->expr()->eq('creator_id', $userParam),
				$qb->expr()->iLike('name', $nameParam),
			)
			->orderBy('name', 'asc');

		return $this->findEntities($qb);
	}

	public function findOneById(int $id): ?Room {
		return $this->findOneBy('id', $id);
	}

	public function findOneByPublicId(string $publicId): ?Room {
		return $this->findOneBy('public_id', $publicId);
	}

	/**
	 * @param string $column
	 * @param mixed $value
	 * @return Room|null
	 */
	private function findOneBy(string $column, $value): ?Room {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName())
			->where(
				$qb->expr()->eq($column, $qb->createNamedParameter($value))
			);

		$rooms = $this->findEntities($qb);
		return empty($rooms) ? null : reset($rooms);
	}
}
