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

use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\OCSController;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\IConfig;
use OCP\IGroup;
use OCP\IGroupManager;
use OCP\IRequest;
use OCP\IUser;
use OCP\IUserSession;
use OCP\Notification\IManager;

class ApiController extends OCSController {

	/** @var IConfig */
	protected $config;

	/** @var ITimeFactory */
	protected $time;

	/** @var IGroupManager */
	protected $groupManager;

	/** @var IManager */
	protected $notificationManager;

	/** @var IUserSession */
	protected $userSession;

	/**
	 * @param string $appName
	 * @param IRequest $request
	 * @param IConfig $config
	 * @param ITimeFactory $time
	 * @param IGroupManager $groupManager
	 * @param IManager $notificationManager
	 * @param IUserSession $userSession
	 */
	public function __construct($appName, IRequest $request, IConfig $config, ITimeFactory $time, IGroupManager $groupManager, IManager $notificationManager, IUserSession $userSession) {
		parent::__construct($appName, $request);

		$this->config = $config;
		$this->time = $time;
		$this->groupManager = $groupManager;
		$this->notificationManager = $notificationManager;
		$this->userSession = $userSession;
	}

	/**
	 * @NoAdminRequired
	 *
	 * @return DataResponse
	 */
	public function pauseForAnHour() {
		$user = $this->userSession->getUser();
		if (!$user instanceof IUser) {
			return new DataResponse([], Http::STATUS_FORBIDDEN);
		}

		$this->config->setUserValue($user->getUID(), 'ransomware_protection', 'disabled_until', (string) ($this->time->getTime() + 3600));

		// Delete the original notification
		$notification = $this->notificationManager->createNotification();
		$notification->setApp('ransomware_protection')
			->setSubject('upload_blocked')
			->setUser($user->getUID());
		$this->notificationManager->markProcessed($notification);

		$notification = $this->notificationManager->createNotification();
		$notification->setApp('ransomware_protection')
			->setSubject('clients_blocked')
			->setUser($user->getUID());
		$this->notificationManager->markProcessed($notification);

		return new DataResponse([], Http::STATUS_ACCEPTED);
	}

	/**
	 * @NoAdminRequired
	 *
	 * @return DataResponse
	 */
	public function reenableProtection() {
		$user = $this->userSession->getUser();
		if (!$user instanceof IUser) {
			return new DataResponse([], Http::STATUS_FORBIDDEN);
		}

		$this->config->setUserValue($user->getUID(), 'ransomware_protection', 'disabled_until', '0');

		return new DataResponse();
	}

	/**
	 * @NoAdminRequired
	 *
	 * @return DataResponse
	 */
	public function requestHelp() {
		$user = $this->userSession->getUser();
		if (!$user instanceof IUser) {
			return new DataResponse([], Http::STATUS_FORBIDDEN);
		}

		$adminGroup = $this->groupManager->get('admin');

		if (!$adminGroup instanceof IGroup) {
			return new DataResponse([], Http::STATUS_INTERNAL_SERVER_ERROR);
		}


		$notification = $this->notificationManager->createNotification();

		$notification->setApp('ransomware_protection')
			->setDateTime(new \DateTime())
			->setObject('user', $user->getUID())
			->setSubject('help_requested', [
				$user->getUID(),
			]);

		foreach ($adminGroup->getUsers() as $admin) {
			if ($admin->getUID() === $user->getUID()) {
				// Don't notify the same user
				continue;
			}

			$notification->setUser($admin->getUID());
			$this->notificationManager->notify($notification);
		}

		// Delete the original notification
		$notification = $this->notificationManager->createNotification();
		$notification->setApp('ransomware_protection')
			->setSubject('upload_blocked')
			->setUser($user->getUID());
		$this->notificationManager->markProcessed($notification);

		return new DataResponse([], Http::STATUS_ACCEPTED);
	}

	/**
	 * @param string $victim
	 * @return DataResponse
	 */
	public function imHelping($victim) {
		$user = $this->userSession->getUser();
		if (!$user instanceof IUser) {
			return new DataResponse([], Http::STATUS_FORBIDDEN);
		}

		$notification = $this->notificationManager->createNotification();

		$notification->setApp('ransomware_protection')
			->setObject('user', $victim);
		$this->notificationManager->markProcessed($notification);

		return new DataResponse([], Http::STATUS_ACCEPTED);
	}
}
