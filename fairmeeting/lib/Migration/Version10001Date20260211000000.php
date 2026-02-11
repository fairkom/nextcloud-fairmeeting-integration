<?php

declare(strict_types=1);

namespace OCA\fairmeeting\Migration;

use Closure;
use Doctrine\DBAL\Schema\SchemaException;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version10001Date20260211000000 extends SimpleMigrationStep {
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

		if ($schema->hasTable('fairmeeting_rooms')) {
			$table = $schema->getTable('fairmeeting_rooms');

			if (!$table->hasColumn('all_start_audio_muted')) {
				$table->addColumn('all_start_audio_muted', Types::BOOLEAN, [
					'notnull' => false,
					'default' => false,
				]);
			}

			if (!$table->hasColumn('all_start_video_muted')) {
				$table->addColumn('all_start_video_muted', Types::BOOLEAN, [
					'notnull' => false,
					'default' => false,
				]);
			}
		}

		return $schema;
	}
}
