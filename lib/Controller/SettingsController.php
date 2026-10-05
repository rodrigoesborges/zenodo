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

use OCA\Zenodo\Service\ConfigService;
use OCP\AppFramework\Controller;
use OCP\IRequest;

class SettingsController extends Controller {

	private ConfigService $configService;

	public function __construct(
		string $appName, IRequest $request, ConfigService $configService
	) {
		parent::__construct($appName, $request);
		$this->configService = $configService;
	}

	public function getZenodoInfo(): array {
		return $this->settings();
	}

	public function setZenodoInfo($token_sandbox, $token_production): array {
		$this->configService->setAppValue(
			ConfigService::TOKEN_SANDBOX, trim((string)$token_sandbox)
		);
		$this->configService->setAppValue(
			ConfigService::TOKEN_PRODUCTION, trim((string)$token_production)
		);

		return $this->settings();
	}

	private function settings(): array {
		return [
			'tokenSandbox' => $this->configService->getAppValue(ConfigService::TOKEN_SANDBOX),
			'tokenProduction' => $this->configService->getAppValue(ConfigService::TOKEN_PRODUCTION),
		];
	}
}
