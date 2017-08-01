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

namespace OCA\RansomwareProtection\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\IConfig;
use OCP\IL10N;
use OCP\IRequest;

class PersonalController extends Controller {

	/** @var IConfig */
	protected $config;

	/** @var ITimeFactory */
	protected $time;

	/** @var IL10N */
	protected $l10n;

	/** @var string */
	protected $userId;

	/**
	 * constructor of the controller
	 *
	 * @param string $appName
	 * @param IRequest $request
	 * @param IConfig $config
	 * @param ITimeFactory $time
	 * @param IL10N $l10n
	 * @param string $userId
	 */
	public function __construct($appName,
								IRequest $request,
								IConfig $config,
								ITimeFactory $time,
								IL10N $l10n,
								$userId) {
		parent::__construct($appName, $request);
		$this->config = $config;
		$this->time = $time;
		$this->l10n = $l10n;
		$this->userId = $userId;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @return TemplateResponse
	 */
	public function displayPanel() {
		$disabledUntil = (int) $this->config->getUserValue($this->userId, 'ransomware_protection', 'disabled_until', 0);
		if ($disabledUntil < $this->time->getTime()) {
			$disabledUntil = 0;
		}
		$disabledUntil = $disabledUntil === 0 ? '' : $this->l10n->l('datetime', $disabledUntil, ['width' => 'medium*|short']);

		return new TemplateResponse('ransomware_protection', 'personal', [
			'disabledUntil'			=> $disabledUntil,
		], '');
	}

}
