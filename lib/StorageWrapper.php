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

use OC\Files\Storage\Wrapper\Wrapper;
use OCP\Files\ForbiddenException;
use OCP\Files\Storage\IStorage;

class StorageWrapper extends Wrapper {

	/** @var Analyzer */
	protected $analyzer;

	/** @var string */
	public $mountPoint;

	/**
	 * @param array $parameters
	 */
	public function __construct($parameters) {
		parent::__construct($parameters);
		$this->analyzer = $parameters['analyzer'];
		$this->mountPoint = $parameters['mountPoint'];
	}

	/*
	 * Storage wrapper methods
	 */

//	/**
//	 * see http://php.net/manual/en/function.mkdir.php
//	 *
//	 * @param string $path
//	 * @return bool
//	 */
//	public function mkdir($path) {
//		return $this->storage->mkdir($path);
//	}
//
//	/**
//	 * see http://php.net/manual/en/function.rmdir.php
//	 *
//	 * @param string $path
//	 * @return bool
//	 */
//	public function rmdir($path) {
//		return $this->storage->rmdir($path);
//	}
//
//	/**
//	 * see http://php.net/manual/en/function.opendir.php
//	 *
//	 * @param string $path
//	 * @return resource
//	 */
//	public function opendir($path) {
//		return $this->storage->opendir($path);
//	}
//
//	/**
//	 * see http://php.net/manual/en/function.is_dir.php
//	 *
//	 * @param string $path
//	 * @return bool
//	 */
//	public function is_dir($path) {
//		return $this->storage->is_dir($path);
//	}
//
//	/**
//	 * see http://php.net/manual/en/function.is_file.php
//	 *
//	 * @param string $path
//	 * @return bool
//	 */
//	public function is_file($path) {
//		return $this->storage->is_file($path);
//	}
//
//	/**
//	 * see http://php.net/manual/en/function.stat.php
//	 * only the following keys are required in the result: size and mtime
//	 *
//	 * @param string $path
//	 * @return array
//	 */
//	public function stat($path) {
//		return $this->storage->stat($path);
//	}
//
//	/**
//	 * see http://php.net/manual/en/function.filetype.php
//	 *
//	 * @param string $path
//	 * @return bool
//	 */
//	public function filetype($path) {
//		return $this->storage->filetype($path);
//	}
//
//	/**
//	 * see http://php.net/manual/en/function.filesize.php
//	 * The result for filesize when called on a folder is required to be 0
//	 *
//	 * @param string $path
//	 * @return int
//	 */
//	public function filesize($path) {
//		return $this->storage->filesize($path);
//	}

	/**
	 * check if a file can be created in $path
	 *
	 * @param string $path
	 * @return bool
	 */
	public function isCreatable($path) {
		try {
			$this->analyzer->checkPath($this, $path, Analyzer::WRITING);
		} catch (ForbiddenException $e) {
			return false;
		}
		return $this->storage->isCreatable($path);
	}

	/**
	 * check if a file can be read
	 *
	 * @param string $path
	 * @return bool
	 */
	public function isReadable($path) {
		try {
			$this->analyzer->checkPath($this, $path, Analyzer::READING);
		} catch (ForbiddenException $e) {
			return false;
		}
		return $this->storage->isReadable($path);
	}

	/**
	 * check if a file can be written to
	 *
	 * @param string $path
	 * @return bool
	 */
	public function isUpdatable($path) {
		try {
			$this->analyzer->checkPath($this, $path, Analyzer::WRITING);
		} catch (ForbiddenException $e) {
			return false;
		}
		return $this->storage->isUpdatable($path);
	}

	/**
	 * check if a file can be deleted
	 *
	 * @param string $path
	 * @return bool
	 */
	public function isDeletable($path) {
		try {
			$this->analyzer->checkPath($this, $path, Analyzer::DELETE);
		} catch (ForbiddenException $e) {
			return false;
		}
		return $this->storage->isDeletable($path);
	}

//	/**
//	 * check if a file can be shared
//	 *
//	 * @param string $path
//	 * @return bool
//	 */
//	public function isSharable($path) {
//		return $this->storage->isSharable($path);
//	}
//
//	/**
//	 * get the full permissions of a path.
//	 * Should return a combination of the PERMISSION_ constants defined in lib/public/constants.php
//	 *
//	 * @param string $path
//	 * @return int
//	 */
//	public function getPermissions($path) {
//		return $this->storage->getPermissions($path);
//	}
//
//	/**
//	 * see http://php.net/manual/en/function.file_exists.php
//	 *
//	 * @param string $path
//	 * @return bool
//	 */
//	public function file_exists($path) {
//		return $this->storage->file_exists($path);
//	}
//
//	/**
//	 * see http://php.net/manual/en/function.filemtime.php
//	 *
//	 * @param string $path
//	 * @return int
//	 */
//	public function filemtime($path) {
//		return $this->storage->filemtime($path);
//	}

	/**
	 * see http://php.net/manual/en/function.file_get_contents.php
	 *
	 * @param string $path
	 * @return string
	 */
	public function file_get_contents($path) {
		$this->analyzer->checkPath($this, $path, Analyzer::READING);
		return $this->storage->file_get_contents($path);
	}

	/**
	 * see http://php.net/manual/en/function.file_put_contents.php
	 *
	 * @param string $path
	 * @param string $data
	 * @return bool
	 */
	public function file_put_contents($path, $data) {
		$this->analyzer->checkPath($this, $path, Analyzer::WRITING);
		return $this->storage->file_put_contents($path, $data);
	}

	/**
	 * see http://php.net/manual/en/function.unlink.php
	 *
	 * @param string $path
	 * @return bool
	 */
	public function unlink($path) {
		$this->analyzer->checkPath($this, $path, Analyzer::DELETE);
		return $this->storage->unlink($path);
	}

	/**
	 * see http://php.net/manual/en/function.rename.php
	 *
	 * @param string $path1
	 * @param string $path2
	 * @return bool
	 */
	public function rename($path1, $path2) {
		$this->analyzer->checkPath($this, $path2, Analyzer::WRITING);
		return $this->storage->rename($path1, $path2);
	}

	/**
	 * see http://php.net/manual/en/function.copy.php
	 *
	 * @param string $path1
	 * @param string $path2
	 * @return bool
	 */
	public function copy($path1, $path2) {
		$this->analyzer->checkPath($this, $path2, Analyzer::WRITING);
		return $this->storage->copy($path1, $path2);
	}

	/**
	 * see http://php.net/manual/en/function.fopen.php
	 *
	 * @param string $path
	 * @param string $mode
	 * @return resource
	 */
	public function fopen($path, $mode) {
		$analyzeMode = Analyzer::READING;
		switch ($mode) {
			case 'r+':
			case 'rb+':
			case 'w+':
			case 'wb+':
			case 'x+':
			case 'xb+':
			case 'a+':
			case 'ab+':
			case 'w':
			case 'wb':
			case 'x':
			case 'xb':
			case 'a':
			case 'ab':
			$analyzeMode = Analyzer::WRITING;
		}
		$this->analyzer->checkPath($this, $path, $analyzeMode);
		return $this->storage->fopen($path, $mode);
	}

//	/**
//	 * get the mimetype for a file or folder
//	 * The mimetype for a folder is required to be "httpd/unix-directory"
//	 *
//	 * @param string $path
//	 * @return string
//	 */
//	public function getMimeType($path) {
//		return $this->storage->getMimeType($path);
//	}
//
//	/**
//	 * see http://php.net/manual/en/function.hash.php
//	 *
//	 * @param string $type
//	 * @param string $path
//	 * @param bool $raw
//	 * @return string
//	 */
//	public function hash($type, $path, $raw = false) {
//		return $this->storage->hash($type, $path, $raw);
//	}
//
//	/**
//	 * see http://php.net/manual/en/function.free_space.php
//	 *
//	 * @param string $path
//	 * @return int
//	 */
//	public function free_space($path) {
//		return $this->storage->free_space($path);
//	}
//
//	/**
//	 * search for occurrences of $query in file names
//	 *
//	 * @param string $query
//	 * @return array
//	 */
//	public function search($query) {
//		return $this->storage->search($query);
//	}

	/**
	 * see http://php.net/manual/en/function.touch.php
	 * If the backend does not support the operation, false should be returned
	 *
	 * @param string $path
	 * @param int $mtime
	 * @return bool
	 */
	public function touch($path, $mtime = null) {
		$this->analyzer->checkPath($this, $path, Analyzer::WRITING);
		return $this->storage->touch($path, $mtime);
	}

//	/**
//	 * get the path to a local version of the file.
//	 * The local version of the file can be temporary and doesn't have to be persistent across requests
//	 *
//	 * @param string $path
//	 * @return string
//	 */
//	public function getLocalFile($path) {
//		return $this->storage->getLocalFile($path);
//	}
//
//	/**
//	 * check if a file or folder has been updated since $time
//	 *
//	 * @param string $path
//	 * @param int $time
//	 * @return bool
//	 *
//	 * hasUpdated for folders should return at least true if a file inside the folder is add, removed or renamed.
//	 * returning true for other changes in the folder is optional
//	 */
//	public function hasUpdated($path, $time) {
//		return $this->storage->hasUpdated($path, $time);
//	}

	/**
	 * get a cache instance for the storage
	 *
	 * @param string $path
	 * @param \OC\Files\Storage\Storage (optional) the storage to pass to the cache
	 * @return \OC\Files\Cache\Cache
	 */
	public function getCache($path = '', $storage = null) {
		if (!$storage) {
			$storage = $this;
		}
		$cache = $this->storage->getCache($path, $storage);
		return new CacheWrapper($cache, $storage, $this->analyzer);
	}

//	/**
//	 * get a scanner instance for the storage
//	 *
//	 * @param string $path
//	 * @param \OC\Files\Storage\Storage (optional) the storage to pass to the scanner
//	 * @return \OC\Files\Cache\Scanner
//	 */
//	public function getScanner($path = '', $storage = null) {
//		if (!$storage) {
//			$storage = $this;
//		}
//		return $this->storage->getScanner($path, $storage);
//	}
//
//
//	/**
//	 * get the user id of the owner of a file or folder
//	 *
//	 * @param string $path
//	 * @return string
//	 */
//	public function getOwner($path) {
//		return $this->storage->getOwner($path);
//	}
//
//	/**
//	 * get a watcher instance for the cache
//	 *
//	 * @param string $path
//	 * @param \OC\Files\Storage\Storage (optional) the storage to pass to the watcher
//	 * @return \OC\Files\Cache\Watcher
//	 */
//	public function getWatcher($path = '', $storage = null) {
//		if (!$storage) {
//			$storage = $this;
//		}
//		return $this->storage->getWatcher($path, $storage);
//	}
//
//	public function getPropagator($storage = null) {
//		if (!$storage) {
//			$storage = $this;
//		}
//		return $this->storage->getPropagator($storage);
//	}
//
//	public function getUpdater($storage = null) {
//		if (!$storage) {
//			$storage = $this;
//		}
//		return $this->storage->getUpdater($storage);
//	}
//
//	/**
//	 * @return \OC\Files\Cache\Storage
//	 */
//	public function getStorageCache() {
//		return $this->storage->getStorageCache();
//	}
//
//	/**
//	 * get the ETag for a file or folder
//	 *
//	 * @param string $path
//	 * @return string
//	 */
//	public function getETag($path) {
//		return $this->storage->getETag($path);
//	}
//
//	/**
//	 * Returns true
//	 *
//	 * @return true
//	 */
//	public function test() {
//		return $this->storage->test();
//	}
//
//	/**
//	 * Returns the wrapped storage's value for isLocal()
//	 *
//	 * @return bool wrapped storage's isLocal() value
//	 */
//	public function isLocal() {
//		return $this->storage->isLocal();
//	}
//
//	/**
//	 * Check if the storage is an instance of $class or is a wrapper for a storage that is an instance of $class
//	 *
//	 * @param string $class
//	 * @return bool
//	 */
//	public function instanceOfStorage($class) {
//		return is_a($this, $class) or $this->storage->instanceOfStorage($class);
//	}
//
//	/**
//	 * Pass any methods custom to specific storage implementations to the wrapped storage
//	 *
//	 * @param string $method
//	 * @param array $args
//	 * @return mixed
//	 */
//	public function __call($method, $args) {
//		return call_user_func_array(array($this->storage, $method), $args);
//	}

	/**
	 * A custom storage implementation can return an url for direct download of a give file.
	 *
	 * For now the returned array can hold the parameter url - in future more attributes might follow.
	 *
	 * @param string $path
	 * @return array
	 */
	public function getDirectDownload($path) {
		$this->analyzer->checkPath($this, $path, Analyzer::READING);
		return $this->storage->getDirectDownload($path);
	}

//	/**
//	 * Get availability of the storage
//	 *
//	 * @return array [ available, last_checked ]
//	 */
//	public function getAvailability() {
//		return $this->storage->getAvailability();
//	}
//
//	/**
//	 * Set availability of the storage
//	 *
//	 * @param bool $isAvailable
//	 */
//	public function setAvailability($isAvailable) {
//		$this->storage->setAvailability($isAvailable);
//	}
//
//	/**
//	 * @param string $path the path of the target folder
//	 * @param string $fileName the name of the file itself
//	 * @return void
//	 * @throws InvalidPathException
//	 */
//	public function verifyPath($path, $fileName) {
//		$this->storage->verifyPath($path, $fileName);
//	}

	/**
	 * @param IStorage $sourceStorage
	 * @param string $sourceInternalPath
	 * @param string $targetInternalPath
	 * @return bool
	 */
	public function copyFromStorage(IStorage $sourceStorage, $sourceInternalPath, $targetInternalPath) {
		if ($sourceStorage === $this) {
			return $this->copy($sourceInternalPath, $targetInternalPath);
		}

		$this->analyzer->checkPath($this, $targetInternalPath, Analyzer::WRITING);
		return $this->storage->copyFromStorage($sourceStorage, $sourceInternalPath, $targetInternalPath);
	}

	/**
	 * @param IStorage $sourceStorage
	 * @param string $sourceInternalPath
	 * @param string $targetInternalPath
	 * @return bool
	 */
	public function moveFromStorage(IStorage $sourceStorage, $sourceInternalPath, $targetInternalPath) {
		if ($sourceStorage === $this) {
			return $this->rename($sourceInternalPath, $targetInternalPath);
		}

		$this->analyzer->checkPath($this, $targetInternalPath, Analyzer::WRITING);
		return $this->storage->moveFromStorage($sourceStorage, $sourceInternalPath, $targetInternalPath);
	}

//	/**
//	 * @param string $path
//	 * @return array
//	 */
//	public function getMetaData($path) {
//		return $this->storage->getMetaData($path);
//	}
//
//	/**
//	 * @param string $path
//	 * @param int $type \OCP\Lock\ILockingProvider::LOCK_SHARED or \OCP\Lock\ILockingProvider::LOCK_EXCLUSIVE
//	 * @param \OCP\Lock\ILockingProvider $provider
//	 * @throws \OCP\Lock\LockedException
//	 */
//	public function acquireLock($path, $type, ILockingProvider $provider) {
//		if ($this->storage->instanceOfStorage('\OCP\Files\Storage\ILockingStorage')) {
//			$this->storage->acquireLock($path, $type, $provider);
//		}
//	}
//
//	/**
//	 * @param string $path
//	 * @param int $type \OCP\Lock\ILockingProvider::LOCK_SHARED or \OCP\Lock\ILockingProvider::LOCK_EXCLUSIVE
//	 * @param \OCP\Lock\ILockingProvider $provider
//	 */
//	public function releaseLock($path, $type, ILockingProvider $provider) {
//		if ($this->storage->instanceOfStorage('\OCP\Files\Storage\ILockingStorage')) {
//			$this->storage->releaseLock($path, $type, $provider);
//		}
//	}
//
//	/**
//	 * @param string $path
//	 * @param int $type \OCP\Lock\ILockingProvider::LOCK_SHARED or \OCP\Lock\ILockingProvider::LOCK_EXCLUSIVE
//	 * @param \OCP\Lock\ILockingProvider $provider
//	 */
//	public function changeLock($path, $type, ILockingProvider $provider) {
//		if ($this->storage->instanceOfStorage('\OCP\Files\Storage\ILockingStorage')) {
//			$this->storage->changeLock($path, $type, $provider);
//		}
//	}
}
