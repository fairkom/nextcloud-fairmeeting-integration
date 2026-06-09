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
 * Adds a `source` column to fairmeeting_rooms so we can distinguish user-
 * created rooms (the room list in the fairmeeting page) from rooms created
 * automatically by the calendar listener. The room list filters on
 * source='manual', which keeps calendar-derived rooms from cluttering the
 * UI even though they live in the same table.
 */
class Version10003Date20260601000000 extends SimpleMigrationStep {
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
		if (!$table->hasColumn('source')) {
			$table->addColumn('source', Types::STRING, [
				'notnull' => true,
				'length' => 16,
				'default' => 'manual',
			]);
		}

		return $schema;
	}
}
