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

return [
	'routes' => [
		[
			'name'     => 'settings#getZenodoInfo',
			'url'      => '/settings',
			'verb'     => 'GET'
		],
		[
			'name'     => 'settings#setZenodoInfo',
			'url'      => '/settings',
			'verb'     => 'POST'
		],
		[
			'name'     => 'Zenodo#dialogZenodo',
			'url'      => '/dialog/{type}',
			'verb'     => 'GET'
		],
		[
			'name'     => 'Zenodo#getZenodoDeposit',
			'url'      => '/deposit',
			'verb'     => 'GET'
		],
		[
			'name'     => 'Zenodo#publishToZenodo',
			'url'      => '/depositions',
			'verb'     => 'POST'
		],
		[
			'name'     => 'Zenodo#uploadToZenodo',
			'url'      => '/depositions/files',
			'verb'     => 'POST'
		],
		[
			'name'     => 'Zenodo#getUnsubmittedDepositionsFromZenodo',
			'url'      => '/depositions/unsubmitted',
			'verb'     => 'GET'
		],
		[
			'name'     => 'Zenodo#getLocalCreator',
			'url'      => '/creator',
			'verb'     => 'GET'
		]
	]
];
