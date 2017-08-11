<?php
/**
 * @copyright Copyright (c) 2017 Joas Schilling <coding@schilljs.com>
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

namespace OCA\RansomwareProtection;


use OCP\App\IAppManager;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\Files\ForbiddenException;
use OCP\Files\Storage\IStorage;
use OCP\IConfig;
use OCP\ILogger;
use OCP\Notification\IManager;

class Striker {

	const FIRST_STRIKE = 1;
	const ALREADY_STRIKED = 2;
	const FIFTH_STRIKE = 3;

	/** @var IConfig */
	protected $config;

	/** @var ITimeFactory */
	protected $time;

	/** @var IManager */
	protected $notifications;

	/** @var ILogger */
	protected $logger;

	/** @var string */
	protected $userId;

	/**
	 * @param IConfig $config
	 * @param ITimeFactory $time
	 * @param IManager $notifications
	 * @param ILogger $logger
	 * @param string $userId
	 */
	public function __construct(IConfig $config, ITimeFactory $time, IManager $notifications, ILogger $logger, $userId) {
		$this->config = $config;
		$this->time = $time;
		$this->notifications = $notifications;
		$this->logger = $logger;
		$this->userId = $userId;
	}

	/**
	 * @param int $mode
	 * @param string $case
	 * @param string $path
	 * @param string $pattern
	 * @throws ForbiddenException
	 */
	public function handleMatch($mode, $case, $path, $pattern) {

		$lastStrikes = $this->config->getUserValue($this->userId, 'ransomware_protection', 'last_strikes', '[]');
		$lastStrikes = json_decode($lastStrikes, true);

		$strikeType = $this->checkLastStrikes($lastStrikes, $path);

		if ($strikeType === self::ALREADY_STRIKED) {
			$this->addRestrikeLog($case, $path, $pattern);
		} else {
			$this->addStrikeLog($case, $path, $pattern);

			if ($mode === Analyzer::WRITING) {
				$this->updateLastStrikes($lastStrikes, [
					'path' => $path,
					'time' => $this->time->getTime(),
				]);
			}
		}

		if ($mode === Analyzer::WRITING) {
			if ($strikeType === self::FIFTH_STRIKE) {
				// Block the user for 1 hour
				$this->config->setUserValue($this->userId, 'ransomware_protection', 'clients_blocked', $this->time->getTime() + 3600);
				$this->notifyUser($path, $pattern, $strikeType);
			}

			if ($strikeType === self::FIRST_STRIKE) {
				$this->notifyUser($path, $pattern, $strikeType);
			}
		}

		throw new ForbiddenException('Ransomware file detected', true);
	}

	/**
	 * @param array $lastStrikes
	 * @param string $path
	 * @return int
	 */
	protected function checkLastStrikes(array $lastStrikes, $path) {
		$thirtyMinutesAgo = $this->time->getTime() - 30 * 60;

		$recentStrikes = 0;
		foreach ($lastStrikes as $strike) {
			if ($strike['path'] === $path && $strike['time'] > $thirtyMinutesAgo) {
				return self::ALREADY_STRIKED;
			}
			if ($strike['time'] > $thirtyMinutesAgo) {
				$recentStrikes++;
			}
		}

		return $recentStrikes > 5 ? self::FIFTH_STRIKE : self::FIRST_STRIKE;
	}

	/**
	 * @param array $lastStrikes
	 * @param array $newStrike
	 */
	protected function updateLastStrikes(array $lastStrikes, $newStrike) {
		$thirtyMinutesAgo = $this->time->getTime() - 30 * 60;

		$lastStrikes = array_filter($lastStrikes, function($strike) use ($thirtyMinutesAgo) {
			return $strike['time'] <= $thirtyMinutesAgo;
		});

		array_unshift($lastStrikes, $newStrike);

		$this->config->setUserValue($this->userId, 'ransomware_protection', 'last_strikes', json_encode($lastStrikes));
	}

	protected function notifyUser($path, $pattern, $strikeType) {
		$notification = $this->notifications->createNotification();

		$notification->setApp('ransomware_protection')
			->setDateTime(new \DateTime())
			->setObject('strike', (string) $strikeType)
			->setSubject($strikeType === self::FIRST_STRIKE ? 'upload_blocked' : 'clients_blocked', [
				$path,
				$pattern,
			])
			->setUser($this->userId);
		$this->notifications->notify($notification);

	}

	/**
	 * @param string $case
	 * @param string $path
	 * @param string $pattern
	 */
	protected function addStrikeLog($case, $path, $pattern) {
		$this->logger->warning(
			'Prevented upload of {path} because it matches {case} pattern "{pattern}"',
			[
				'case' => $case,
				'path' => $path,
				'pattern' => $pattern,
				'app' => 'ransomware_protection',
			]
		);
	}

	/**
	 * @param string $case
	 * @param string $path
	 * @param string $pattern
	 * @throws ForbiddenException
	 */
	protected function addRestrikeLog($case, $path, $pattern) {
		$this->logger->info(
			'Prevented repeated upload of {path} because it matches {case} pattern "{pattern}"',
			[
				'case' => $case,
				'path' => $path,
				'pattern' => $pattern,
				'app' => 'ransomware_protection',
			]
		);
	}
}
