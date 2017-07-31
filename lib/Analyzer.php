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

class Analyzer {

	/** @var string[] */
	protected $extensionsPlain;
	/** @var int[] */
	protected $extensionsPlainLength;
	/** @var string[] */
	protected $extensionsRegex;

	/** @var string[] */
	protected $notesPlain;
	/** @var string[] */
	protected $notesRegex;

	/** @var string[] */
	protected $notesBiasedPlain;
	/** @var string[] */
	protected $notesBiasedRegex;

	/** @var IConfig */
	protected $config;

	/** @var ITimeFactory */
	protected $time;

	/** @var IAppManager */
	protected $appManager;

	/** @var ILogger */
	protected $logger;

	/** @var Striker */
	protected $striker;

	/** @var string */
	protected $userId;

	/** @var int */
	protected $nestingLevel = 0;

	/**
	 * @param IConfig $config
	 * @param ITimeFactory $time
	 * @param IAppManager $appManager
	 * @param ILogger $logger
	 * @param Striker $striker
	 * @param string $userId
	 */
	public function __construct(IConfig $config, ITimeFactory $time, IAppManager $appManager, ILogger $logger, Striker $striker, $userId) {
		$this->config = $config;
		$this->time = $time;
		$this->appManager = $appManager;
		$this->logger = $logger;
		$this->striker = $striker;
		$this->userId = $userId;
	}

	protected function parseResources() {
		$resourcesPath = $this->appManager->getAppPath('ransomware_protection') . 'resources/';

		$extensions = explode("\n", file_get_contents($resourcesPath . 'extensions.txt'));
		foreach ($extensions as $ext) {
			if (empty($ext)) {
				continue;
			}

			if (strpos($ext, '^') === 0 || substr($ext, -1) === '$') {
				$this->extensionsRegex[] = $ext;
				continue;
			}

			$this->extensionsPlain[] = $ext;
			$this->extensionsPlainLength[$ext] = strlen($ext);
		}

		$notes = explode("\n", file_get_contents($resourcesPath . 'notes.txt'));
		foreach ($notes as $note) {
			if (empty($note)) {
				continue;
			}

			if (strpos($note, '^') === 0 || substr($note, -1) === '$') {
				$this->notesRegex[] = $note;
				continue;
			}

			$this->notesPlain[] = $note;
		}

		$this->notesBiasedRegex = $this->notesBiasedPlain = [];
		if ($this->config->getAppValue('ransomware_protection', 'check-all', 'no') === 'yes') {
			$notes = explode("\n", file_get_contents($resourcesPath . 'notes-biased.txt'));
			foreach ($notes as $note) {
				if (empty($note)) {
					continue;
				}

				if (strpos($note, '^') === 0 || substr($note, -1) === '$') {
					$this->notesBiasedRegex[] = $note;
					continue;
				}

				$this->notesBiasedPlain[] = $note;
			}
		}
	}

	/**
	 * @param IStorage $storage
	 * @param string $path
	 * @throws ForbiddenException
	 */
	public function checkPath(IStorage $storage, $path) {
		if ($this->userId === null || $this->nestingLevel !== 0 || !$this->isBlockablePath($storage, $path) || $this->isCreatingSkeletonFiles()) {
			// Allow creating skeletons and theming
			return;
		}

		if ($this->config->getUserValue($this->userId, 'ransomware_protection', 'disabled_until', 0) < $this->time->getTime()) {
			// Protection is currently disabled for the user
			return;
		}

		$this->nestingLevel++;

		$filePath = $path;
		if (property_exists($storage, 'mountPoint')) {
			/** @var StorageWrapper $storage */
			$filePath = $storage->mountPoint . $path;
		}

		// '', admin, 'files', 'path/to/file.txt'
		$segment = explode('/', $filePath, 4);
		$userPath = $segment[3];
		$fileName = basename($userPath);

		$this->checkExtension($fileName, $userPath, $this->extensionsPlain, $this->extensionsRegex, $this->extensionsPlainLength);
		$this->checkNotes($fileName, $userPath, $this->notesPlain, $this->notesRegex);
		if ($this->config->getAppValue('ransomware_protection', 'check-all', 'no') === 'yes') {
			$this->checkNotes($fileName, $userPath, $this->notesBiasedPlain, $this->notesBiasedRegex);
		}
	}

	/**
	 * Check if a file name matches the prefix/extension
	 *
	 * @param string $name
	 * @param string $path
	 * @param string[] $plain
	 * @param string[] $regex
	 * @param int[] $plainLengths
	 * @throws ForbiddenException
	 */
	protected function checkExtension($name, $path, array $plain, array $regex, array $plainLengths) {
		foreach ($plain as $ext) {
			if (strpos($ext, '.') === 0 || strpos($ext, '_') === 0) {
				if (isset($plainLengths[$ext]) && substr($name, $plainLengths[$ext]) === $ext) {
					$this->striker->handleMatch('extension', $path, $ext);
				}
			} else if (strpos($name, $ext) !== false) {
				$this->striker->handleMatch('extension', $path, $ext);
			}
		}

		foreach ($regex as $ext) {
			if (preg_match('/' . $ext . '/', $name) === 1) {
				$this->striker->handleMatch('extension', $path, $ext);
			}
		}
	}

	/**
	 * Check if a file name matches the info/notes file
	 *
	 * @param string $name
	 * @param string $path
	 * @param string[] $plain
	 * @param string[] $regex
	 * @throws ForbiddenException
	 */
	protected function checkNotes($name, $path, array $plain, array $regex) {
		foreach ($plain as $note) {
			if ($name === $note) {
				$this->striker->handleMatch('note file', $path, $note);
			}
		}

		foreach ($regex as $note) {
			if (preg_match('/' . $note . '/', $name) === 1) {
				$this->striker->handleMatch('note file', $path, $note);
			}
		}
	}

	/**
	 * @param IStorage $storage
	 * @param string $path
	 * @return bool
	 */
	protected function isBlockablePath(IStorage $storage, $path) {
		$fullPath = $path;
		if (property_exists($storage, 'mountPoint')) {
			/** @var StorageWrapper $storage */
			$fullPath = $storage->mountPoint . $path;
		}

		if (substr_count($fullPath, '/') < 3) {
			return false;
		}

		// '', admin, 'files', 'path/to/file.txt'
		$segment = explode('/', $fullPath, 4);

		return isset($segment[2]) && in_array($segment[2], [
			'files',
			'thumbnails',
			'files_versions',
		], true);
	}

	/**
	 * For thumbnails and versions we want to check the tags of the original file
	 *
	 * @param IStorage $storage
	 * @param string $path
	 * @return bool
	 */
	protected function translatePath(IStorage $storage, $path) {
		if (substr_count($path, '/') < 1) {
			return $path;
		}

		// 'files', 'path/to/file.txt'
		list($folder, $innerPath) = explode('/', $path, 2);

		if ($folder === 'files_versions') {
			$innerPath = substr($innerPath, 0, strrpos($innerPath, '.v'));
			return 'files/' . $innerPath;
		}

		if ($folder === 'thumbnails') {
			list($fileId,) = explode('/', $innerPath, 2);
			$innerPath = $storage->getCache()->getPathById($fileId);

			if ($innerPath !== null) {
				return 'files/' . $innerPath;
			}
		}

		return $path;
	}

	/**
	 * Check if we are in the LoginController and if so, ignore the firewall
	 * @return bool
	 */
	protected function isCreatingSkeletonFiles() {
		$exception = new \Exception();
		$trace = $exception->getTrace();

		foreach ($trace as $step) {
			if (isset($step['class'], $step['function']) &&
				$step['class'] === 'OC\Core\Controller\LoginController' &&
				$step['function'] === 'tryLogin') {
				return true;
			}
		}

		return false;
	}
}
