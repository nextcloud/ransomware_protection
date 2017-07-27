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

class Analyzer {

	/** @var string[] */
	protected $extensionsPlain;
	/** @var int[] */
	protected $extensionsPlainLength;
	/** @var string[] */
	protected $extensionsRegex;

	/** @var IConfig */
	protected $config;

	/** @var ITimeFactory */
	protected $time;

	/** @var IAppManager */
	protected $appManager;

	/** @var int */
	protected $nestingLevel = 0;

	/**
	 * @param IConfig $config
	 * @param ITimeFactory $time
	 * @param IAppManager $appManager
	 */
	public function __construct(IConfig $config, ITimeFactory $time, IAppManager $appManager) {
		$this->config = $config;
		$this->time = $time;
		$this->appManager = $appManager;
	}

	protected function parseExtensions() {
		$pathToList = $this->appManager->getAppPath('ransomware_protection') . 'resources/extensions.txt';
		$extensions = explode("\n", file_get_contents($pathToList));

		foreach ($extensions as $ext) {
			if (strpos($ext, '^') === 0 || substr($ext, -1) === '$') {
				$this->extensionsRegex[] = $ext;
				continue;
			}

			$this->extensionsPlain[] = $ext;
			$this->extensionsPlainLength[$ext] = strlen($ext);
		}
	}

	/**
	 * @param IStorage $storage
	 * @param string $path
	 * @throws ForbiddenException
	 */
	public function checkPath(IStorage $storage, $path) {
		if ($this->nestingLevel !== 0 || !$this->isBlockablePath($storage, $path) || $this->isCreatingSkeletonFiles()) {
			// Allow creating skeletons and theming
			return;
		}

		if ($this->config->getUserValue(\OC_User::getUser(), 'ransomware_protection', 'disabled_until', 0) < $this->time->getTime()) {
			// Protection is currently disabled for the user
			return;
		}

		$this->nestingLevel++;

		$filePath = $this->translatePath($storage, $path);
		$fileName = basename($filePath);

		foreach ($this->extensionsPlain as $ext) {
			if (substr($fileName, $this->extensionsPlainLength[$ext]) === $ext) {
				throw new ForbiddenException('Ransomware file detected', true);
			}
		}

		foreach ($this->extensionsRegex as $ext) {
			if (preg_match('/' . $ext . '/', $fileName) === 1) {
				throw new ForbiddenException('Ransomware file detected', true);
			}
		}
	}

	/**
	 * Check if a file name matches the prefix/extension
	 * @param string $name
	 * @param string $ext
	 * @return bool
	 */
	protected function checkExtension($name, $ext) {
		if (strpos($ext, '^') === 0 || substr($ext, -1) === '$') {
			return preg_match('/' . $ext . '/', $name) === 1;
		}

		if (strpos($ext, '.') === 0 || strpos($ext, '_') === 0) {
			return substr($name, 0 - strlen($ext)) === $ext;
		}

		return strpos($name, $ext) !== false;
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
