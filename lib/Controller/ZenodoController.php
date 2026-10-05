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

namespace OCA\Zenodo\Controller;

use OCA\Zenodo\Db\DepositionFiles;
use OCA\Zenodo\Db\DepositionFilesMapper;
use OCA\Zenodo\Exceptions\ZenodoApiException;
use OCA\Zenodo\Service\ApiService;
use OCA\Zenodo\Service\FileService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\Files\File;
use OCP\IRequest;
use OCP\IUserManager;
use OCP\IUserSession;
use Psr\Log\LoggerInterface;

class ZenodoController extends Controller {

	public function __construct(
		string $appName,
		IRequest $request,
		private IUserSession $userSession,
		private IUserManager $userManager,
		private ApiService $apiService,
		private FileService $fileService,
		private DepositionFilesMapper $depositionFilesMapper,
		private LoggerInterface $logger
	) {
		parent::__construct($appName, $request);
	}

	#[NoAdminRequired]
	public function dialogZenodo(string $type): TemplateResponse {
		$template = match ($type) {
			'AddFile' => 'dialog.addfile',
			default => 'dialog.newdeposition',
		};

		return new TemplateResponse($this->appName, $template, [], 'blank');
	}

	#[NoAdminRequired]
	public function getZenodoDeposit($fileId, $filename): array {
		$deposition = $this->depositionFilesMapper->findFile((int)$fileId);

		return [
			'fileId' => (int)$fileId,
			'filename' => $filename,
			'type' => ($deposition !== null) ? $deposition->getType() : '',
			'depositId' => ($deposition !== null) ? (int)$deposition->getDepositId() : 0
		];
	}

	#[NoAdminRequired]
	public function getLocalCreator($username): array {
		if ($username === '_self') {
			$username = $this->getUserId();
		}

		$user = $this->userManager->get($username);

		return [
			'realname' => ($user !== null) ? $user->getDisplayName() : $username,
			'orcid' => $this->getOrcid($username)
		];
	}

	#[NoAdminRequired]
	public function getUnsubmittedDepositionsFromZenodo(): array {
		$result = ['success' => true, 'data' => [], 'errors' => []];

		foreach ([false, true] as $production) {
			$this->apiService->init($production);
			if (!$this->apiService->isConfigured()) {
				continue;
			}

			try {
				$drafts = $this->apiService->listDrafts();
			} catch (ZenodoApiException $e) {
				$result['errors'][] = ($production ? 'production: ' : 'sandbox: ') . $e->getMessage();
				continue;
			}

			foreach ($drafts as $draft) {
				$deposition = $this->depositionFilesMapper->findDeposit((int)$draft->id);
				if ($deposition === null || $deposition->getUserId() !== $this->getUserId()) {
					continue;
				}

				$result['data'][] = [
					'id' => (int)$draft->id,
					'title' => $draft->metadata->title ?? ('#' . $draft->id),
					'production' => $production
				];
			}
		}

		return $result;
	}

	#[NoAdminRequired]
	public function publishToZenodo($fileId, array $metadata, $production): array {
		$production = ($production === true || $production === 'true');
		$this->apiService->init($production);

		if (!$this->apiService->isConfigured()) {
			return $this->error('No Zenodo token defined. Please contact your administrator.');
		}

		$node = $this->getFileNode($fileId);
		if ($node === null) {
			return $this->error('File not found.');
		}

		try {
			$draft = $this->apiService->createDraft($metadata);
			$this->uploadNode((int)$draft->id, $node);
		} catch (ZenodoApiException $e) {
			return $this->error($e->getMessage());
		}

		$this->storeDepositionFile((int)$fileId, (int)$draft->id, $production ? 'prod' : 'sandbox');

		return [
			'success' => true,
			'depositUrl' => $draft->links->self_html ?? null,
			'depositId' => (int)$draft->id
		];
	}

	#[NoAdminRequired]
	public function uploadToZenodo($depositId, $fileId, $production): array {
		$deposition = $this->depositionFilesMapper->findDeposit((int)$depositId);
		if ($deposition === null || $deposition->getUserId() !== $this->getUserId()) {
			return $this->error('This is not your deposition.');
		}

		$production = ($production === true || $production === 'true');
		$this->apiService->init($production);

		if (!$this->apiService->isConfigured()) {
			return $this->error('No Zenodo token defined. Please contact your administrator.');
		}

		$node = $this->getFileNode($fileId);
		if ($node === null) {
			return $this->error('File not found.');
		}

		try {
			$this->uploadNode((int)$depositId, $node);
		} catch (ZenodoApiException $e) {
			return $this->error($e->getMessage());
		}

		return ['success' => true, 'depositId' => (int)$depositId];
	}

	private function getUserId(): string {
		return $this->userSession->getUser()?->getUID() ?? '';
	}

	private function getFileNode($fileId): ?File {
		return $this->fileService->getFilesPerFileId((int)$fileId)[0] ?? null;
	}

	/**
	 * @throws ZenodoApiException
	 */
	private function uploadNode(int $depositId, File $node): void {
		$local = $this->fileService->getLocalFile($node);
		if ($local === null) {
			throw new ZenodoApiException('Cannot access the content of the file.');
		}

		try {
			$this->apiService->uploadFile($depositId, $local['path'], $node->getName());
		} finally {
			if ($local['temporary']) {
				@unlink($local['path']);
			}
		}
	}

	private function storeDepositionFile(int $fileId, int $depositId, string $type): void {
		$this->depositionFilesMapper->deleteFile($fileId, false);

		$deposition = new DepositionFiles();
		$deposition->setUserId($this->getUserId());
		$deposition->setFileId($fileId);
		$deposition->setType($type);
		$deposition->setDepositId($depositId);

		$this->depositionFilesMapper->insert($deposition);
	}

	private function getOrcid(string $username): string {
		try {
			if (!\OCP\App::isEnabled('orcid')) {
				return '';
			}

			return (string)\OCA\Orcid\Service\ApiService::getUserOrcid($username);
		} catch (\Throwable $e) {
			$this->logger->debug('could not retrieve the ORCID of a user', [
				'username' => $username,
				'exception' => $e,
			]);

			return '';
		}
	}

	private function error(string $message): array {
		return ['success' => false, 'error' => $message];
	}
}
