/**
 * @copyright Copyright (c) 2026
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
 *
 */

import axios from '@nextcloud/axios'
import { t } from '@nextcloud/l10n'
import { generateUrl } from '@nextcloud/router'

const sandboxToken = document.getElementById('sandboxtoken')
const productionToken = document.getElementById('productiontoken')
const submitButton = document.getElementById('tokensubmit')
const status = document.getElementById('tokensubmit-status')

const setStatus = (message, state) => {
	status.textContent = message
	status.className = state
}

const load = async() => {
	try {
		const response = await axios.get(generateUrl('/apps/zenodo/settings'))
		sandboxToken.value = response.data.tokenSandbox ?? ''
		productionToken.value = response.data.tokenProduction ?? ''
	} catch (error) {
		setStatus(t('zenodo', 'Failed to load the current credentials.'), 'error')
	}
}

submitButton.addEventListener('click', async() => {
	setStatus('', '')
	try {
		await axios.post(generateUrl('/apps/zenodo/settings'), {
			token_sandbox: sandboxToken.value,
			token_production: productionToken.value,
		})
		setStatus(t('zenodo', 'Credentials stored.'), 'success')
	} catch (error) {
		setStatus(t('zenodo', 'Failed to store the credentials.'), 'error')
	}
})

load()
