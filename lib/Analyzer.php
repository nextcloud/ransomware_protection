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
use OCP\IRequest;

class Analyzer {
	public const READING = 1;
	public const WRITING = 2;
	public const DELETE = 3;

	/** @var string[] */
	protected $extensionsPlain = [];
	/** @var int[] */
	protected $extensionsPlainLength = [];
	/** @var string[] */
	protected $extensionsRegex = [];

	/** @var string[] */
	protected $notesPlain = [];
	/** @var string[] */
	protected $notesRegex = [];

	/** @var string[] */
	protected $notesBiasedPlain = [];
	/** @var string[] */
	protected $notesBiasedRegex = [];

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

	/** @var IRequest */
	protected $request;

	/**
	 * @param IRequest $request
	 * @param IConfig $config
	 * @param ITimeFactory $time
	 * @param IAppManager $appManager
	 * @param ILogger $logger
	 * @param Striker $striker
	 * @param string $userId
	 */
	public function __construct(IRequest $request, IConfig $config, ITimeFactory $time, IAppManager $appManager, ILogger $logger, Striker $striker, $userId) {
		$this->request = $request;
		$this->config = $config;
		$this->time = $time;
		$this->appManager = $appManager;
		$this->logger = $logger;
		$this->striker = $striker;
		$this->userId = $userId;
	}

	protected function ensureResourcesAreLoaded() {
		if (empty($this->extensionsPlain)) {
			$this->parseResources();
		}
	}

	protected function parseResources() {
		$resourcesPath = $this->appManager->getAppPath('ransomware_protection') . '/resources/';

		$extensionExclusions = $this->config->getAppValue('ransomware_protection', 'extension_exclusions', '[]');
		$extensionExclusions = json_decode($extensionExclusions, true);
		$extensionAdditions = $this->config->getAppValue('ransomware_protection', 'extension_additions', '[]');
		$extensionAdditions = json_decode($extensionAdditions, true);

		$extensions = explode("\n", file_get_contents($resourcesPath . 'extensions.txt'));
		$extensions = array_diff($extensions, $extensionExclusions);
		$extensions = array_merge($extensions, $extensionAdditions);

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


		$noteExclusions = $this->config->getAppValue('ransomware_protection', 'notefile_exclusions', '[]');
		$noteExclusions = json_decode($noteExclusions, true);
		$noteAdditions = $this->config->getAppValue('ransomware_protection', 'notefile_additions', '[]');
		$noteAdditions = json_decode($noteAdditions, true);

		$notes = explode("\n", file_get_contents($resourcesPath . 'notes.txt'));
		$notes = array_diff($notes, $noteExclusions);
		$notes = array_merge($notes, $noteAdditions);

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
		if ($this->config->getAppValue('ransomware_protection', 'notes_include_biased', 'no') === 'yes') {
			$notes = explode("\n", file_get_contents($resourcesPath . 'notes-biased.txt'));
			$notes = array_diff($notes, $noteExclusions);

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
	 * @param int $mode
	 * @throws ForbiddenException
	 */
	public function checkPath(IStorage $storage, $path, $mode) {
		if ($this->userId === null || $this->nestingLevel !== 0 || !$this->isBlockablePath($storage, $path) || $this->isCreatingSkeletonFiles()) {
			// Allow creating skeletons and theming
			return;
		}

		if (!$this->request->isUserAgent([
			IRequest::USER_AGENT_CLIENT_DESKTOP,
			IRequest::USER_AGENT_CLIENT_ANDROID,
			IRequest::USER_AGENT_CLIENT_IOS,
		])) {
			// Not a sync client
			return;
		}

		if ($this->config->getUserValue($this->userId, 'ransomware_protection', 'disabled_until', 0) >= $this->time->getTime()) {
			// Protection is currently disabled for the user
			return;
		}

		if ($this->config->getUserValue($this->userId, 'ransomware_protection', 'clients_blocked', 0) >= $this->time->getTime()) {
			throw new ForbiddenException('Sync clients blocked by ransomware protection', true);
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

		$this->ensureResourcesAreLoaded();

		try {
			$this->checkExtension($mode, $fileName, $userPath, $this->extensionsPlain, $this->extensionsRegex, $this->extensionsPlainLength);
			$this->checkNotes($mode, $fileName, $userPath, $this->notesPlain, $this->notesRegex);
			if ($this->config->getAppValue('ransomware_protection', 'notes_include_biased', 'no') === 'yes') {
				$this->checkNotes($mode, $fileName, $userPath, $this->notesBiasedPlain, $this->notesBiasedRegex);
			}
		} catch (ForbiddenException $e) {
			/** @var IStorage $storage */
			if ($storage->getMimeType($path) !== 'httpd/unix-directory') {
				$this->nestingLevel--;
				throw $e;
			}
		}

		$this->nestingLevel--;
	}

	/**
	 * Check if a file name matches the prefix/extension
	 *
	 * @param int $mode
	 * @param string $name
	 * @param string $path
	 * @param string[] $plain
	 * @param string[] $regex
	 * @param int[] $plainLengths
	 * @throws ForbiddenException
	 */
	protected function checkExtension($mode, $name, $path, array $plain, array $regex, array $plainLengths) {
		foreach ($plain as $ext) {
			if (strpos($ext, '.') === 0 || strpos($ext, '_') === 0) {
				if (isset($plainLengths[$ext]) && substr($name, 0 - $plainLengths[$ext]) === $ext) {
					$this->striker->handleMatch($mode, 'extension', $path, $ext);
				}
			} elseif (strpos($name, $ext) !== false) {
				$this->striker->handleMatch($mode, 'extension', $path, $ext);
			}
		}

		foreach ($regex as $ext) {
			if (preg_match('/' . $ext . '/', $name) === 1) {
				$this->striker->handleMatch($mode, 'extension', $path, $ext);
			}
		}
	}

	/**
	 * Check if a file name matches the info/notes file
	 *
	 * @param int $mode
	 * @param string $name
	 * @param string $path
	 * @param string[] $plain
	 * @param string[] $regex
	 * @throws ForbiddenException
	 */
	protected function checkNotes($mode, $name, $path, array $plain, array $regex) {
		foreach ($plain as $note) {
			if ($name === $note) {
				$this->striker->handleMatch($mode, 'note file', $path, $note);
			}
		}

		foreach ($regex as $note) {
			if (preg_match('/' . $note . '/', $name) === 1) {
				$this->striker->handleMatch($mode, 'note file', $path, $note);
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
	 * @return string
	 */
	protected function translatePath(IStorage $storage, string $path): string {
		if (substr_count($path, '/') < 1) {
			return $path;
		}

		// 'files', 'path/to/file.txt'
		[$folder, $innerPath] = explode('/', $path, 2);

		if ($folder === 'files_versions') {
			$innerPath = substr($innerPath, 0, strrpos($innerPath, '.v'));
			return 'files/' . $innerPath;
		}

		if ($folder === 'thumbnails') {
			[$fileId,] = explode('/', $innerPath, 2);
			$innerPath = $storage->getCache()->getPathById((int) $fileId);

			if ($innerPath !== null) {
				return 'files/' . $innerPath;
			}
		}

		return $path;
	}

	/**
	 * Check if we are in the LoginController and if so, ignore the firewall
	 */
	protected function isCreatingSkeletonFiles(): bool {
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
