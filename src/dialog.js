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

const alertBox = (message, title) => {
	if (window.OC?.dialogs?.alert) {
		window.OC.dialogs.alert(message, title)
	} else {
		window.alert(message)
	}
}

const fetchDialogTemplate = async(type) => {
	const response = await axios.get(generateUrl(`/apps/zenodo/dialog/${type}`), {
		responseType: 'text',
	})
	return response.data
}

const buildModal = (title, templateHtml) => {
	const overlay = document.createElement('div')
	overlay.className = 'zenodo-modal-overlay'

	const dialog = document.createElement('div')
	dialog.id = 'zenodo-dialog'
	dialog.setAttribute('role', 'dialog')
	dialog.setAttribute('aria-modal', 'true')

	const titleElement = document.createElement('h3')
	titleElement.id = 'zenodo-dialog-title'
	titleElement.textContent = title

	const loading = document.createElement('div')
	loading.id = 'zenodo-loading'
	loading.classList.add('hidden')

	const content = document.createElement('div')
	content.innerHTML = templateHtml

	const error = document.createElement('div')
	error.id = 'zenodo_error'
	error.classList.add('hidden')

	const footer = document.createElement('div')
	footer.className = 'zenodo-modal-footer'

	dialog.append(titleElement, loading, content, error, footer)
	overlay.appendChild(dialog)
	document.body.appendChild(overlay)

	const close = () => overlay.remove()
	overlay.addEventListener('click', (event) => {
		if (event.target === overlay) {
			close()
		}
	})
	overlay.addEventListener('keydown', (event) => {
		if (event.key === 'Escape') {
			close()
		}
	})

	const showError = (message) => {
		error.textContent = message
		error.classList.remove('hidden')
	}
	const hideError = () => error.classList.add('hidden')
	const setLoading = (isLoading) => {
		loading.classList.toggle('hidden', !isLoading)
		dialog.setAttribute('aria-busy', isLoading ? 'true' : 'false')
	}
	const addFooterButton = (label, primary, onClick) => {
		const button = document.createElement('button')
		button.className = primary ? 'zenodo-btn zenodo-btn-primary' : 'zenodo-btn'
		button.type = 'button'
		button.textContent = label
		button.addEventListener('click', onClick)
		footer.appendChild(button)
		return button
	}
	const showResult = (message, url) => {
		content.textContent = ''
		const paragraph = document.createElement('p')
		paragraph.textContent = message
		if (url) {
			const link = document.createElement('a')
			link.href = url
			link.target = '_blank'
			link.rel = 'noreferrer noopener'
			link.textContent = url
			content.append(paragraph, link)
		} else {
			content.appendChild(paragraph)
		}
	}

	return { overlay, dialog, footer, close, showError, hideError, setLoading, addFooterButton, showResult }
}

const requestError = (error, fallback) => {
	return error?.response?.data?.error ?? (error?.message ?? fallback)
}

export const openNewDepositionDialog = async(node) => {
	try {
		const response = await axios.get(generateUrl('/apps/zenodo/deposit'), {
			params: { fileId: node.id, filename: node.basename },
		})
		const deposit = response.data
		if (deposit.depositId !== 0 && deposit.type === 'prod') {
			alertBox(
				t('zenodo', 'This file has already been published to the production Zenodo.'),
				t('zenodo', 'Zenodo'),
			)
			return
		}
	} catch (error) {
		// the dialog can still be opened, the publication will fail later if needed
	}

	const templateHtml = await fetchDialogTemplate('NewDeposition')
	const modal = buildModal(t('zenodo', 'New Zenodo deposition'), templateHtml)

	const uploadType = modal.dialog.querySelector('#zendialog_uploadtype')
	const publicationType = modal.dialog.querySelector('#zendialog_publicationtype')
	const imageType = modal.dialog.querySelector('#zendialog_imagetype')
	const publicationDate = modal.dialog.querySelector('#zendialog_publicationdate')
	const title = modal.dialog.querySelector('#zendialog_title')
	const description = modal.dialog.querySelector('#zendialog_description')
	const accessRight = modal.dialog.querySelector('#zendialog_accessright')
	const license = modal.dialog.querySelector('#zendialog_license')
	const embargoRow = modal.dialog.querySelector('#zendialog_display_embargodate')
	const embargoDate = modal.dialog.querySelector('#zendialog_embargodate')

	const addCreator = modal.dialog.querySelector('#zendialog_create_add')
	const addSelf = modal.dialog.querySelector('#zendialog_sub')
	const creatorRealname = modal.dialog.querySelector('#zendialog_creator_realname')
	const creatorOrcid = modal.dialog.querySelector('#zendialog_creator_orcid')
	const creatorSyntaxError = modal.dialog.querySelector('#zendialog_creator_syntax_error')
	const creatorsList = modal.dialog.querySelector('#zendialog_creators_list')

	const creators = []

	const refreshTypes = () => {
		publicationType.style.display = uploadType.value === 'publication' ? '' : 'none'
		imageType.style.display = uploadType.value === 'image' ? '' : 'none'
	}
	uploadType.addEventListener('change', refreshTypes)
	refreshTypes()

	const refreshEmbargo = () => {
		embargoRow.style.display = accessRight.value === 'embargoed' ? '' : 'none'
	}
	accessRight.addEventListener('change', refreshEmbargo)
	refreshEmbargo()

	const refreshCreators = () => {
		creatorsList.textContent = ''
		creators.forEach((creator, index) => {
			const item = document.createElement('div')
			item.id = 'zenodo_creator_item'

			const label = document.createElement('span')
			label.textContent = creator.orcid
				? t('zenodo', '{name} (ORCID: {orcid})', { name: creator.name, orcid: creator.orcid })
				: creator.name

			const remove = document.createElement('span')
			remove.textContent = ' ✕'
			remove.style.cursor = 'pointer'
			remove.addEventListener('click', () => {
				creators.splice(index, 1)
				refreshCreators()
			})

			item.append(label, remove)
			creatorsList.appendChild(item)
		})
	}

	addCreator.textContent = '+'
	addCreator.title = t('zenodo', 'Add creator')
	addCreator.addEventListener('click', () => {
		const name = creatorRealname.value.trim()
		if (!name.includes(',') || name.split(',')[1].trim() === '') {
			creatorSyntaxError.classList.remove('hidden')
			return
		}
		creatorSyntaxError.classList.add('hidden')
		creators.push({ name, orcid: creatorOrcid.value.trim() })
		creatorRealname.value = ''
		creatorOrcid.value = ''
		refreshCreators()
	})
	creatorSyntaxError.classList.add('hidden')

	addSelf.addEventListener('click', async() => {
		try {
			const response = await axios.get(generateUrl('/apps/zenodo/creator'), {
				params: { username: '_self' },
			})
			creatorRealname.value = response.data.realname ?? ''
			creatorOrcid.value = response.data.orcid ?? ''
		} catch (error) {
			modal.showError(requestError(error, t('zenodo', 'Could not retrieve your local creator information.')))
		}
	})

	const submit = async(production) => {
		if (title.value.trim() === '') {
			modal.showError(t('zenodo', 'Please provide a title.'))
			return
		}
		if (uploadType.value === '') {
			modal.showError(t('zenodo', 'Please select an upload type.'))
			return
		}
		if (publicationDate.value === '') {
			modal.showError(t('zenodo', 'Please select a publication date.'))
			return
		}
		if (creators.length === 0) {
			modal.showError(t('zenodo', 'Please add at least one creator.'))
			return
		}
		if (accessRight.value === 'embargoed' && embargoDate.value === '') {
			modal.showError(t('zenodo', 'Please select the embargo date.'))
			return
		}

		const metadata = {
			uploadType: uploadType.value,
			publicationType: publicationType.value,
			imageType: imageType.value,
			publicationDate: publicationDate.value,
			title: title.value.trim(),
			description: description.value,
			creators: creators.map((creator) => ({ name: creator.name, orcid: creator.orcid })),
			accessRight: accessRight.value || 'open',
			embargoDate: embargoDate.value,
			license: license.value,
		}

		modal.hideError()
		modal.setLoading(true)
		try {
			const response = await axios.post(generateUrl('/apps/zenodo/depositions'), {
				fileId: Number(node.id),
				metadata,
				production,
			})
			if (response.data.success !== true) {
				throw new Error(response.data.error)
			}
			modal.footer.textContent = ''
			modal.addFooterButton(t('zenodo', 'Close'), false, modal.close)
			modal.showResult(
				t('zenodo', 'The draft deposition was created on Zenodo. You can review and publish it on zenodo.org:'),
				response.data.depositUrl,
			)
		} catch (error) {
			modal.showError(requestError(error, t('zenodo', 'Failed to create the deposition on Zenodo.')))
		} finally {
			modal.setLoading(false)
		}
	}

	modal.addFooterButton(t('zenodo', 'Cancel'), false, modal.close)
	modal.addFooterButton(t('zenodo', 'Send to sandbox'), false, () => submit(false))
	modal.addFooterButton(t('zenodo', 'Send to production'), true, () => submit(true))
}

export const openAddFileDialog = async(node) => {
	const templateHtml = await fetchDialogTemplate('AddFile')
	const modal = buildModal(t('zenodo', 'Add file to a Zenodo deposition'), templateHtml)

	const select = modal.dialog.querySelector('#zendialog_deposition')
	const drafts = []

	modal.setLoading(true)
	try {
		const response = await axios.get(generateUrl('/apps/zenodo/depositions/unsubmitted'))
		const data = response.data.data ?? []
		if (data.length === 0) {
			modal.showError(t('zenodo', 'No unsubmitted deposition found. Please create a new deposition first.'))
		}
		data.forEach((draft) => {
			const option = document.createElement('option')
			option.value = String(drafts.length)
			option.textContent = `[${draft.production ? t('zenodo', 'production') : t('zenodo', 'sandbox')}] ${draft.title} (#${draft.id})`
			select.appendChild(option)
			drafts.push(draft)
		})
		;(response.data.errors ?? []).forEach((draftError) => {
			modal.showError(draftError)
		})
	} catch (error) {
		modal.showError(requestError(error, t('zenodo', 'Failed to retrieve the list of unsubmitted depositions.')))
	} finally {
		modal.setLoading(false)
	}

	const submit = async() => {
		const draft = drafts[parseInt(select.value, 10)]
		if (!draft) {
			modal.showError(t('zenodo', 'Please select a deposition.'))
			return
		}

		modal.hideError()
		modal.setLoading(true)
		try {
			const response = await axios.post(generateUrl('/apps/zenodo/depositions/files'), {
				depositId: draft.id,
				fileId: Number(node.id),
				production: draft.production,
			})
			if (response.data.success !== true) {
				throw new Error(response.data.error)
			}
			modal.footer.textContent = ''
			modal.addFooterButton(t('zenodo', 'Close'), false, modal.close)
			modal.showResult(t('zenodo', 'The file was uploaded to the deposition. You can review it on zenodo.org.'))
		} catch (error) {
			modal.showError(requestError(error, t('zenodo', 'Failed to upload the file to the deposition.')))
		} finally {
			modal.setLoading(false)
		}
	}

	modal.addFooterButton(t('zenodo', 'Cancel'), false, modal.close)
	modal.addFooterButton(t('zenodo', 'Upload'), true, () => submit())
}
