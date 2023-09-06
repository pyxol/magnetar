<?php
	declare(strict_types=1);
	
	namespace Magnetar\Filesystem\Driver;
	
	use Magnetar\Filesystem\Adapter\Adapter;
	
	/**
	 * Base class for filesystem drivers
	 */
	class Driver {
		public function __construct(
			/**
			 * Filesystem adapter
			 * @var Adapter
			 */
			protected Adapter $adapter
		) {
			
		}
		
		/**
		 * Convert a path relative to the root directory to a full path
		 * @param string $path Path relative to root directory
		 * @return string Full path
		 */
		public function fullpath(string $path): string {
			return $this->adapter->path($path);
		}
		
		/**
		 * Remove the leading root directory from a full path
		 * @param string $path Full path to strip the root path from
		 * @return string Path relative to root directory
		 */
		public function unrootPath(string $path): string {
			if(str_starts_with($path, $this->adapter->rootDir())) {
				$path = substr($path, strlen($this->adapter->rootDir()));
			}
			
			return ltrim($path, DIRECTORY_SEPARATOR);
		}
	}