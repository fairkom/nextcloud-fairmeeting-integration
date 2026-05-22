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
 * Adds per-room overrides for the global "skip prejoin" / "disable deep linking"
 * meeting URL options. Both columns are nullable: NULL means "fall back to the
 * global admin default", so existing rooms keep current behavior.
 */
class Version10002Date20260522000000 extends SimpleMigrationStep {
	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure
	 * @param array<mixed> $options
	 * @return ISchemaWrapper
	 * @throws SchemaException
	 */
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

		if (!$table->hasColumn('skip_prejoin')) {
			$table->addColumn('skip_prejoin', Types::BOOLEAN, [
				'notnull' => false,
				'default' => null,
			]);
		}

		if (!$table->hasColumn('disable_deep_linking')) {
			$table->addColumn('disable_deep_linking', Types::BOOLEAN, [
				'notnull' => false,
				'default' => null,
			]);
		}

		return $schema;
	}
}
