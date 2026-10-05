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

?>
<div id="zendialog_content">

	<table>
		<tr>
			<td class="zendialog_left"><?php p($l->t('Upload type:')); ?></td>
			<td>

				<select id="zendialog_uploadtype">
					<option value=""></option>
					<option value="publication"><?php p($l->t('Publication')); ?></option>
					<option value="poster"><?php p($l->t('Poster')); ?></option>
					<option value="presentation"><?php p($l->t('Presentation')); ?></option>
					<option value="dataset"><?php p($l->t('Dataset')); ?></option>
					<option value="image"><?php p($l->t('Image')); ?></option>
					<option value="video"><?php p($l->t('Video/Audio')); ?></option>
					<option value="software"><?php p($l->t('Software')); ?></option>
					<option value="lesson"><?php p($l->t('Lesson')); ?></option>
					<option value="other"><?php p($l->t('Other')); ?></option>
				</select>

				<select id="zendialog_publicationtype">
					<option value="book"><?php p($l->t('Book')); ?></option>
					<option value="section"><?php p($l->t('Book section')); ?></option>
					<option value="conferencepaper"><?php p($l->t('Conference paper')); ?></option>
					<option value="article"><?php p($l->t('Journal article')); ?></option>
					<option value="patent"><?php p($l->t('Patent')); ?></option>
					<option value="preprint"><?php p($l->t('Preprint')); ?></option>
					<option value="deliverable"><?php p($l->t('Project deliverable')); ?></option>
					<option value="milestone"><?php p($l->t('Project milestone')); ?></option>
					<option value="proposal"><?php p($l->t('Proposal')); ?></option>
					<option value="report"><?php p($l->t('Report')); ?></option>
					<option value="softwaredocumentation"><?php p($l->t('Software documentation')); ?></option>
					<option value="thesis"><?php p($l->t('Thesis')); ?></option>
					<option value="technicalnote"><?php p($l->t('Technical note')); ?></option>
					<option value="workingpaper"><?php p($l->t('Working paper')); ?></option>
					<option value="other"><?php p($l->t('Other')); ?></option>
				</select>
				<select id="zendialog_imagetype">
					<option value="figure"><?php p($l->t('Figure')); ?></option>
					<option value="plot"><?php p($l->t('Plot')); ?></option>
					<option value="drawing"><?php p($l->t('Drawing')); ?></option>
					<option value="diagram"><?php p($l->t('Diagram')); ?></option>
					<option value="photo"><?php p($l->t('Photo')); ?></option>
					<option value="other"><?php p($l->t('Other')); ?></option>
				</select>
			</td>
		</tr>

		<tr>
			<td class="zendialog_left"><?php p($l->t('Publication date:')); ?></td>
			<td><input type="date" id="zendialog_publicationdate"></td>
		</tr>

		<tr>
			<td class="zendialog_left"><?php p($l->t('Title:')); ?></td>
			<td><input type="text" id="zendialog_title" style="width: 400px"></td>
		</tr>

		<tr>
			<td class="zendialog_left" style="vertical-align: top; padding-top: 10px;"><?php p($l->t('Description:')); ?></td>
			<td><textarea id="zendialog_description" style="width: 400px; height: 60px;"></textarea>
			</td>
		</tr>

		<tr>
			<td class="zendialog_left zendialog_creator_left">
				<?php p($l->t('Creators:')); ?><br/>
				<span class="zendialog_sub" id="zendialog_sub"><?php p($l->t('add yourself as creator')); ?></span><br/>
			</td>
			<td class="zendialog_creator">
				<div><input type="text" id="zendialog_creator_realname" placeholder="Lastname, Firstname"
							style="width: 244px">
					<input type="text" id="zendialog_creator_orcid" placeholder="ORCID" style="width: 150px">
					<div id="zendialog_create_add"></div>
				</div>
				<div id="zendialog_creator_syntax_error"><?php p($l->t('Name of creator must be in the format')); ?>
					<b><?php p($l->t('Family name, Given names')); ?></b></div>
				<div id="zendialog_creators_list"></div>
			</td>
		</tr>

		<tr>
			<td class="zendialog_left"><?php p($l->t('Access right:')); ?></td>
			<td>
				<select id="zendialog_accessright">
					<option value=""></option>
					<option value="open"><?php p($l->t('Open')); ?></option>
					<option value="embargoed"><?php p($l->t('Embargoed')); ?></option>
					<option value="restricted"><?php p($l->t('Restricted')); ?></option>
					<option value="closed"><?php p($l->t('Closed')); ?></option>
				</select>

				<select id="zendialog_license">
					<option value=""><?php p($l->t('Choose a license')); ?></option>
					<option value="cc-by-4.0">Creative Commons Attribution 4.0</option>
					<option value="cc-by-sa-4.0">Creative Commons Attribution-ShareAlike 4.0</option>
					<option value="cc-by-nc-4.0">Creative Commons Attribution-NonCommercial 4.0</option>
					<option value="cc0-1.0">CC0 1.0 Universal</option>
					<option value="mit">MIT License</option>
					<option value="apache-2.0">Apache License 2.0</option>
					<option value="gpl-3.0">GNU General Public License v3.0</option>
					<option value="agpl-3.0">GNU Affero General Public License v3.0</option>
				</select>
			</td>
		</tr>
		<tr id="zendialog_display_embargodate">
			<td class="zendialog_left"><?php p($l->t('Embargo date:')); ?></td>
			<td><input type="date" id="zendialog_embargodate">
			</td>
		</tr>

	</table>


</div>
