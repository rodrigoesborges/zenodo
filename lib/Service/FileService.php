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

namespace OCA\Zenodo\Service;

use OCP\Files\File;
use OCP\Files\IRootFolder;
use OCP\Files\Node;
use OCP\IUserSession;

class FileService {

	public function __construct(
		private IUserSession $userSession, private IRootFolder $rootFolder
	) {
	}

	/**
	 * Returns the file nodes matching a file id for the current user.
	 *
	 * @return File[]
	 */
	public function getFilesPerFileId(int $fileId): array {
		$userId = $this->getUserId();
		if ($userId === '' || $fileId === 0) {
			return [];
		}

		try {
			$userFolder = $this->rootFolder->getUserFolder($userId);
			$nodes = $userFolder->getById($fileId);
		} catch (\Exception $e) {
			return [];
		}

		return array_values(array_filter(
			$nodes,
			static fn (Node $node) => $node instanceof File
		));
	}

	/**
	 * Returns a local absolute path for the file. If the file is not stored on
	 * a local storage (remote mount, ...), a temporary copy is created and
	 * 'temporary' is set to true so that the caller can delete it afterwards.
	 *
	 * @return array{path: string, temporary: bool}|null
	 */
	public function getLocalFile(File $file): ?array {
		try {
			if ($file->getStorage()->isLocal()) {
				$path = $file->getStorage()->getLocalFile($file->getInternalPath());
				if (is_string($path) && $path !== '') {
					return ['path' => $path, 'temporary' => false];
				}
			}

			$temporary = tempnam(sys_get_temp_dir(), 'zenodo_');
			if ($temporary === false) {
				return null;
			}

			$source = $file->fopen('rb');
			$target = fopen($temporary, 'wb');
			if ($source === false || $target === false) {
				if (is_resource($source)) {
					fclose($source);
				}
				if (is_resource($target)) {
					fclose($target);
				}
				@unlink($temporary);

				return null;
			}

			stream_copy_to_stream($source, $target);
			fclose($source);
			fclose($target);

			return ['path' => $temporary, 'temporary' => true];
		} catch (\Exception $e) {
			return null;
		}
	}

	private function getUserId(): string {
		return $this->userSession->getUser()?->getUID() ?? '';
	}
}
