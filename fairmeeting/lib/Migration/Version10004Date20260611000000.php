<?php

declare(strict_types=1);

namespace OCA\fairmeeting\Migration;

use Closure;
use Doctrine\DBAL\Schema\SchemaException;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * Adds activity columns to fairmeeting_rooms so the room list can show
 * "created X ago" and "last joined Y ago", and sort by recency.
 */
class Version10004Date20260611000000 extends SimpleMigrationStep {
	public function changeSchema(
		IOutput $output,
		Closure $schemaClosure,
		array $options
	) {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();
		if (!$schema->hasTable('fairmeeting_rooms')) {
			return $schema;
		}
		$table = $schema->getTable('fairmeeting_rooms');
		if (!$table->hasColumn('created_at')) {
			$table->addColumn('created_at', Types::DATETIME, [
				'notnull' => false,
				'default' => null,
			]);
		}
		if (!$table->hasColumn('last_joined_at')) {
			$table->addColumn('last_joined_at', Types::DATETIME, [
				'notnull' => false,
				'default' => null,
			]);
		}
		return $schema;
	}
}
