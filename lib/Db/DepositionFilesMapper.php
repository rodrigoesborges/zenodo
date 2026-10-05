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

namespace OCA\Zenodo\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

/**
 * @template-extends QBMapper<DepositionFiles>
 */
class DepositionFilesMapper extends QBMapper {

	public const TABLENAME = 'zenodo_depositions_files';

	public function __construct(IDBConnection $db) {
		parent::__construct($db, self::TABLENAME, DepositionFiles::class);
	}

	public function find(int $id): ?DepositionFiles {
		return $this->findOneBy('id', $id);
	}

	public function findFile(int $fileId): ?DepositionFiles {
		return $this->findOneBy('file_id', $fileId);
	}

	public function findDeposit(int $depositId): ?DepositionFiles {
		return $this->findOneBy('deposit_id', $depositId);
	}

	/**
	 * Delete the rows related to a file. If $force is false, only the rows
	 * pointing to the sandbox are deleted, keeping the history of the files
	 * already published on the production Zenodo.
	 */
	public function deleteFile(int $fileId, bool $force): void {
		$qb = $this->db->getQueryBuilder();
		$qb->delete(self::TABLENAME)
			->where(
				$qb->expr()->eq(
					'file_id', $qb->createNamedParameter($fileId, IQueryBuilder::PARAM_INT)
				)
			);

		if (!$force) {
			$qb->andWhere(
				$qb->expr()->eq('type', $qb->createNamedParameter('sandbox'))
			);
		}

		$qb->executeStatement();
	}

	private function findOneBy(string $column, int $value): ?DepositionFiles {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from(self::TABLENAME)
			->where(
				$qb->expr()->eq(
					$column, $qb->createNamedParameter($value, IQueryBuilder::PARAM_INT)
				)
			)
			->setMaxResults(1);

		return $this->findEntities($qb)[0] ?? null;
	}
}
