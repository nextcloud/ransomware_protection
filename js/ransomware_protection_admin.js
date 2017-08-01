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

$(document).ready(function() {
	var timeouts = {
			'extension_additions': undefined,
			'notefile_additions': undefined,
			'extension_exclusions': undefined,
			'notefile_exclusions': undefined
		},
		savingCustomValues = function(fieldId, $field) {


			var patterns = $field.val()
				.split("\n")
				.map(function(pattern) { return pattern.trim(); })
				.filter(function(pattern) { return pattern !== ''; });
			OCP.AppConfig.setValue('ransomware_protection', fieldId, JSON.stringify(patterns), {
				success: function() {
					$('#loading_' + fieldId).addClass('hidden');
					$('#saved_' + fieldId).removeClass('hidden');
					setTimeout(function() {
						$('#saved_' + fieldId).addClass('hidden');
					}, 2500);
				}
			});
		};

	$('#ransomware_protection').find('textarea').on('change input paste keyup', function(e) {
		var $field = $(e.currentTarget),
			fieldId = $field.attr('id');

		if (!_.isUndefined(timeouts[fieldId])) {
			clearTimeout(timeouts[fieldId]);
		}

		$('#loading_' + fieldId).removeClass('hidden');
		timeouts[fieldId] = setTimeout(_.bind(savingCustomValues, this, fieldId, $field), 1500);
	});

	$('#ransomware_protection_notes_include_biased').change(function() {
		OCP.AppConfig.setValue('ransomware_protection', 'notes_include_biased', (this.checked ? 'yes' : 'no'));
	});
});
