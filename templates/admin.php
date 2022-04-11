<?php
/**
 * @copyright Copyright (c) 2017 Joas Schilling <coding@schilljs.com>
 *
 * @author Joas Schilling <coding@schilljs.com>
 *
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


/** @var array $_ */
/** @var \OCP\IL10N $l */
style('ransomware_protection', 'ransomware_protection');
script('ransomware_protection', 'ransomware_protection_admin');
?>
<div id="ransomware_protection" class="section">
	<h2 class="inlineblock"><?php p($l->t('Ransomware protection')); ?></h2>



	<p>
		<input id="ransomware_protection_notes_include_biased" name="notes_include_biased"
			   type="checkbox" class="checkbox" value="1"
			<?php if ($_['notesIncludeBiased']) {
	print_unescaped('checked="checked"');
} ?> />
		<label for="ransomware_protection_notes_include_biased"><?php p($l->t('Include note files with non-obvious names, e.g. ReadMe.TxT, info.html')); ?></label><br/>
	</p>

	<div class="custom-list">
		<h3>
			<?php p($l->t('Additional extension patterns')); ?>
			<span id="loading_extension_additions" class="icon icon-loading-small-dark hidden"></span>
			<span id="saved_extension_additions" class="icon icon-checkmark-color hidden"></span>
		</h3>

		<p><?php p($l->t('One pattern per line. If the pattern is a regular expression it has to start with ^ or end with $. Leading dot or underscore on non-regular expression patterns mean that the name has to end with the given string.')); ?></p>
		<textarea id="extension_additions" title="<?php p($l->t('Additional extension patterns')); ?>"><?php p($_['extensionAdditions']); ?></textarea>
	</div>

	<div class="custom-list">
		<h3>
			<?php p($l->t('Additional note file patterns')); ?>
			<span id="loading_notefile_additions" class="icon icon-loading-small-dark hidden"></span>
			<span id="saved_notefile_additions" class="icon icon-checkmark-color hidden"></span>
		</h3>

		<p><?php p($l->t('One pattern per line. If the pattern is a regular expression it has to start with ^ or end with $ otherwise the name must be a complete match.')); ?></p>
		<textarea id="notefile_additions" title="<?php p($l->t('Additional note file patterns')); ?>"><?php p($_['noteFileAdditions']); ?></textarea>
	</div>

	<div class="custom-list">
		<h3>
			<?php p($l->t('Exclude extension patterns')); ?>
			<span id="loading_extension_exclusions" class="icon icon-loading-small-dark hidden"></span>
			<span id="saved_extension_exclusions" class="icon icon-checkmark-color hidden"></span>
		</h3>

		<p><?php p($l->t('One pattern per line. Copy the exact string from the resource file. This helps keeping your exclusions while updating the app.')); ?></p>
		<textarea id="extension_exclusions" title="<?php p($l->t('Ignore extension patterns')); ?>"><?php p($_['extensionExclusions']); ?></textarea>
	</div>

	<div class="custom-list">
		<h3>
			<?php p($l->t('Exclude note file patterns')); ?>
			<span id="loading_notefile_exclusions" class="icon icon-loading-small-dark hidden"></span>
			<span id="saved_notefile_exclusions" class="icon icon-checkmark-color hidden"></span>
		</h3>

		<p><?php p($l->t('One pattern per line. Copy the exact string from the resource file. This helps keeping your exclusions while updating the app.')); ?></p>
		<textarea id="notefile_exclusions" title="<?php p($l->t('Ignore note file patterns')); ?>"><?php p($_['noteFileExclusions']); ?></textarea>
	</div>
</div>
