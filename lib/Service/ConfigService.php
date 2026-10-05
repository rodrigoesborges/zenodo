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

use OCA\Zenodo\AppInfo\Application;
use OCP\IConfig;

class ConfigService {

	public const TOKEN_SANDBOX = 'tokenSandbox';
	public const TOKEN_PRODUCTION = 'tokenProduction';

	public function __construct(private IConfig $config) {
	}

	public function getAppValue(string $key): string {
		return $this->config->getAppValue(Application::APP_ID, $key, '');
	}

	public function setAppValue(string $key, string $value): void {
		$this->config->setAppValue(Application::APP_ID, $key, $value);
	}

	public function deleteAppValue(string $key): void {
		$this->config->deleteAppValue(Application::APP_ID, $key);
	}
}
