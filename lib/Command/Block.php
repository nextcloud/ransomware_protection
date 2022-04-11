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

namespace OCA\RansomwareProtection\Command;

use OCA\RansomwareProtection\Striker;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\IConfig;
use OCP\IUserManager;
use OCP\Notification\IManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Block extends Command {

	/** @var ITimeFactory */
	protected $time;

	/** @var IUserManager */
	protected $userManager;

	/** @var IConfig */
	protected $config;

	/** @var IManager */
	protected $notifications;

	/**
	 * @param ITimeFactory $timeFactory
	 * @param IUserManager $userManager
	 * @param IConfig $config
	 * @param IManager $notificationManager
	 */
	public function __construct(ITimeFactory $timeFactory, IUserManager $userManager, IConfig $config, IManager $notificationManager) {
		parent::__construct();

		$this->time = $timeFactory;
		$this->userManager = $userManager;
		$this->config = $config;
		$this->notifications = $notificationManager;
	}

	protected function configure() {
		$this
			->setName('ransomware_protection:block')
			->setDescription('Block a user from syncing further files')
			->addArgument(
				'user-id',
				InputArgument::REQUIRED,
				'User ID of the user to block'
			)
		;
	}

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @return int
	 * @throws \OCP\PreConditionNotMetException
	 * @throws \InvalidArgumentException
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$userId = $input->getArgument('user-id');

		if (!$this->userManager->userExists($userId)) {
			$output->writeln('<error>Given user does not exist</error>');
			return 1;
		}

		$this->config->setUserValue($userId, 'ransomware_protection', 'clients_blocked', $this->time->getTime() + 3600);

		$notification = $this->notifications->createNotification();

		$notification->setApp('ransomware_protection')
			->setDateTime(new \DateTime())
			->setObject('strike', Striker::EXTERNAL_STRIKE)
			->setSubject('clients_blocked', [
				'',
				'external script',
			])
			->setUser($userId);
		$this->notifications->notify($notification);

		return 0;
	}
}
