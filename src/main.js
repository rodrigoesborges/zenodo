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

import { registerFileAction } from '@nextcloud/files'
import { mdiCloudUploadOutline, mdiFileDocumentPlusOutline } from '@mdi/js'
import { t } from '@nextcloud/l10n'
import { openAddFileDialog, openNewDepositionDialog } from './dialog.js'

const renderIcon = (path) => `<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="${path}" /></svg>`

registerFileAction({
	id: 'zenodo-newdeposition',
	displayName: ({ nodes }) => t('zenodo', 'New Zenodo deposition'),
	iconSvgInline: () => renderIcon(mdiCloudUploadOutline),
	enabled: ({ nodes }) => nodes.length === 1 && nodes[0].type === 'file',
	async exec({ nodes }) {
		await openNewDepositionDialog(nodes[0])
		return null
	},
})

registerFileAction({
	id: 'zenodo-addfile',
	displayName: ({ nodes }) => t('zenodo', 'Add file to a Zenodo deposition'),
	iconSvgInline: () => renderIcon(mdiFileDocumentPlusOutline),
	enabled: ({ nodes }) => nodes.length === 1 && nodes[0].type === 'file',
	async exec({ nodes }) {
		await openAddFileDialog(nodes[0])
		return null
	},
})
