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

use OCA\Zenodo\Exceptions\ZenodoApiException;

/**
 * Client for the current Zenodo (InvenioRDM) REST API.
 *
 * The app only creates draft records and uploads files to them; the final
 * review and publication is done by the user on zenodo.org.
 */
class ApiService {

	private const API_SANDBOX = 'https://sandbox.zenodo.org/api';
	private const API_PRODUCTION = 'https://zenodo.org/api';

	private ConfigService $configService;
	private bool $production = false;
	private string $token = '';

	public function __construct(ConfigService $configService) {
		$this->configService = $configService;
	}

	/**
	 * Select the target (sandbox or production) and load its token.
	 */
	public function init(bool $production): void {
		$this->production = $production;
		$this->token = $production
			? $this->configService->getAppValue(ConfigService::TOKEN_PRODUCTION)
			: $this->configService->getAppValue(ConfigService::TOKEN_SANDBOX);
	}

	public function isConfigured(): bool {
		return $this->token !== '';
	}

	/**
	 * Create a new draft record on Zenodo. Returns the API draft object
	 * (contains id and links, including links->self_html).
	 *
	 * @throws ZenodoApiException
	 */
	public function createDraft(array $form): object {
		$result = $this->request(
			'POST', '/records', [
				'json' => $this->buildDraftPayload($form),
			]
		);

		if (!isset($result->id)) {
			throw new ZenodoApiException('Unexpected response from Zenodo while creating the draft record.');
		}

		return $result;
	}

	/**
	 * Upload a local file to a draft record, using the initialize/upload/commit
	 * flow of the InvenioRDM files API.
	 *
	 * @throws ZenodoApiException
	 */
	public function uploadFile(int $depositId, string $localPath, string $fileName): void {
		$this->request(
			'POST', sprintf('/records/%d/draft/files', $depositId), [
				'json' => [['key' => $fileName]],
			]
		);

		$this->request(
			'PUT', sprintf('/records/%d/draft/files/%s/content', $depositId, rawurlencode($fileName)), [
				'file' => $localPath,
			]
		);

		$this->request(
			'POST', sprintf('/records/%d/draft/files/%s/commit', $depositId, rawurlencode($fileName))
		);
	}

	/**
	 * List the draft (unpublished) records of the configured Zenodo account.
	 *
	 * @return object[]
	 * @throws ZenodoApiException
	 */
	public function listDrafts(): array {
		$result = $this->request('GET', '/user/records?size=100&sort=newest');
		$hits = $result->hits->hits ?? [];

		$drafts = [];
		foreach ($hits as $hit) {
			if (($hit->status ?? 'draft') !== 'published') {
				$drafts[] = $hit;
			}
		}

		return $drafts;
	}

	/**
	 * Build the payload for POST /api/records from the form data sent by the
	 * front-end.
	 */
	private function buildDraftPayload(array $form): array {
		$metadata = [
			'title' => trim((string)($form['title'] ?? '')),
			'publication_date' => trim((string)($form['publicationDate'] ?? '')) ?: date('Y-m-d'),
			'description' => trim((string)($form['description'] ?? '')),
			'creators' => $this->mapCreators($form['creators'] ?? []),
			'resource_type' => ['id' => $this->mapResourceType($form)],
		];

		$license = trim((string)($form['license'] ?? ''));
		if ($license !== '') {
			$metadata['rights'] = [['id' => $license]];
		}

		return [
			'access' => $this->mapAccess($form),
			'files' => ['enabled' => true],
			'metadata' => $metadata,
		];
	}

	private function mapResourceType(array $form): string {
		$type = (string)($form['uploadType'] ?? 'other');

		if ($type === 'publication') {
			$sub = trim((string)($form['publicationType'] ?? ''));

			return 'publication-' . ($sub !== '' ? $sub : 'other');
		}

		if ($type === 'image') {
			$sub = trim((string)($form['imageType'] ?? ''));

			return 'image-' . ($sub !== '' ? $sub : 'other');
		}

		return $type !== '' ? $type : 'other';
	}

	private function mapCreators(array $creators): array {
		$result = [];
		foreach ($creators as $creator) {
			$name = trim((string)($creator['name'] ?? ''));
			if ($name === '') {
				continue;
			}

			if (str_contains($name, ',')) {
				[$family, $given] = array_map('trim', explode(',', $name, 2));
				$person = [
					'type' => 'personal',
					'family_name' => $family,
					'given_name' => $given,
				];
			} else {
				$person = [
					'type' => 'organizational',
					'name' => $name,
				];
			}

			$orcid = trim((string)($creator['orcid'] ?? ''));
			if ($orcid !== '') {
				$person['identifiers'] = [
					['scheme' => 'orcid', 'identifier' => $orcid],
				];
			}

			$result[] = ['person_or_org' => $person];
		}

		return $result;
	}

	/**
	 * Map the legacy "access right" concept to the InvenioRDM access model:
	 * - open: everything public
	 * - embargoed: metadata public, files restricted until the embargo date
	 * - restricted/closed: everything restricted
	 */
	private function mapAccess(array $form): array {
		$right = (string)($form['accessRight'] ?? 'open');

		switch ($right) {
			case 'embargoed':
				return [
					'record' => 'public',
					'files' => 'restricted',
					'embargo' => [
						'active' => true,
						'until' => trim((string)($form['embargoDate'] ?? '')),
						'reason' => null,
					],
				];

			case 'restricted':
			case 'closed':
				return [
					'record' => 'restricted',
					'files' => 'restricted',
				];

			default:
				return [
					'record' => 'public',
					'files' => 'public',
				];
		}
	}

	private function baseUrl(): string {
		return $this->production ? self::API_PRODUCTION : self::API_SANDBOX;
	}

	/**
	 * @param string $method HTTP method
	 * @param string $path API path, starting with a slash
	 * @param array $options 'json' (array to send as JSON) or 'file' (path to
	 *                        a file to send as raw bytes)
	 *
	 * @return object decoded JSON response
	 * @throws ZenodoApiException
	 */
	private function request(string $method, string $path, array $options = []): object {
		$curl = curl_init($this->baseUrl() . $path);

		$headers = [
			'Authorization: Bearer ' . $this->token,
			'Accept: application/json',
			'User-Agent: nextcloud-zenodo/2.0',
		];

		$fileHandle = null;
		if (isset($options['json'])) {
			$headers[] = 'Content-Type: application/json';
			curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($options['json']));
		} elseif (isset($options['file'])) {
			$headers[] = 'Content-Type: application/octet-stream';
			$fileHandle = fopen($options['file'], 'rb');
			curl_setopt($curl, CURLOPT_INFILE, $fileHandle);
			curl_setopt($curl, CURLOPT_INFILESIZE, (int)filesize($options['file']));
		}

		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($curl, CURLOPT_TIMEOUT, 300);

		$body = curl_exec($curl);
		$status = (int)curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
		$error = curl_error($curl);
		curl_close($curl);

		if ($fileHandle !== null) {
			fclose($fileHandle);
		}

		if ($body === false) {
			throw new ZenodoApiException('Connection to Zenodo failed: ' . $error, 0);
		}

		$data = json_decode((string)$body);

		if ($status >= 400) {
			throw new ZenodoApiException($this->extractErrorMessage($data, $status), $status);
		}

		return $data ?? new \stdClass();
	}

	private function extractErrorMessage(?object $data, int $status): string {
		$message = 'Zenodo returned an error (HTTP ' . $status . ')';

		if ($data !== null && isset($data->message) && is_string($data->message)) {
			$message = $data->message;
		}

		if ($data !== null && !empty($data->errors)) {
			$first = $data->errors[0];
			$field = (string)($first->field ?? '');
			$detail = (string)($first->messages[0] ?? '');
			if ($field !== '' || $detail !== '') {
				$message .= ': ' . trim($field . ' ' . $detail);
			}
		}

		return $message;
	}
}
