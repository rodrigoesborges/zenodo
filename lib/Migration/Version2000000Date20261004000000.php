<?php
/**
 * Zenodo - Publish your work to Zenodo.org
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Maxence Lange <maxence@pontapreta.net>
 * @copyright 2017
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace OCA\Zenodo\Migration;

use Closure;
use Doctrine\DBAL\Types\Types;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version2000000Date20261004000000 extends SimpleMigrationStep {

	public function change(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if ($schema->hasTable('zenodo_depositions_files')) {
			return null;
		}

		$table = $schema->createTable('zenodo_depositions_files');
		$table->addColumn('id', Types::INTEGER, [
			'autoincrement' => true,
			'unsigned' => true,
			'notnull' => true,
		]);
		$table->addColumn('file_id', Types::BIGINT, [
			'unsigned' => true,
			'notnull' => true,
		]);
		$table->addColumn('user_id', Types::STRING, [
			'length' => 64,
			'notnull' => true,
		]);
		$table->addColumn('type', Types::STRING, [
			'length' => 8,
			'notnull' => true,
			'default' => 'sandbox',
		]);
		$table->addColumn('deposit_id', Types::BIGINT, [
			'unsigned' => true,
			'notnull' => true,
		]);
		$table->setPrimaryKey(['id']);
		$table->addIndex(['file_id'], 'zenodo_df_file_id');
		$table->addIndex(['deposit_id'], 'zenodo_df_deposit_id');

		return $schema;
	}
}
