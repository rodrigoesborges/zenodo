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

script('zenodo', 'zenodo-admin');
style('zenodo', 'admin');

?>
<div class="section" id="zenodo">
	<h2><?php p($l->t('Zenodo')) ?></h2>

	<p class="zenodo-settings-hint">
		<?php p($l->t('Generate a token with the "deposit:write" scope on sandbox.zenodo.org (sandbox) and on zenodo.org (production), and store them below. All requests are sent with the account of the token owner.')); ?>
	</p>

	<p>
		<label for="sandboxtoken"><?php p($l->t('Sandbox token')) ?></label><br />
		<input type="password" autocomplete="off" id="sandboxtoken" />
	</p>
	<p>
		<label for="productiontoken"><?php p($l->t('Production token')) ?></label><br />
		<input type="password" autocomplete="off" id="productiontoken" />
	</p>

	<p>
		<button id="tokensubmit"><?php p($l->t('Store Zenodo credentials')) ?></button>
		<span id="tokensubmit-status"></span>
	</p>
</div>
