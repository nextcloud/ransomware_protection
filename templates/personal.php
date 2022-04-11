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
script('ransomware_protection', 'ransomware_protection');
?>
<div id="ransomware_protection" class="section">
	<h2 class="inlineblock"><?php p($l->t('Ransomware protection')); ?></h2>

	<p id="ransomware_protection_protected" class="<?php if ($_['disabledUntil']) {
	p('hidden');
} ?>">
		<span class="icon icon-checkmark-color svg"></span>
		<?php p($l->t('Protection is currently active')); ?>
	</p>

	<p id="ransomware_protection_paused" class="<?php if (!$_['disabledUntil']) {
	p('hidden');
} ?>">
		<span class="icon icon-error-color svg"></span>
		<span><?php print_unescaped($l->t('Protection is currently paused until: <strong>%s</strong>', $_['disabledUntil'])); ?></span><br>

		<button id="ransomware_protection_reenable"><?php p($l->t('Re-enable protection now')); ?></button>
	</p>

</div>
