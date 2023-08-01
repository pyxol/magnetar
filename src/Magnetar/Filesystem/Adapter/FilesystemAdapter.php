<?php
	declare(strict_types=1);
	
	namespace Magnetar\Filesystem\Adapter;
	
	class FilesystemAdapter implements AdapterInterface {
		public function __construct(
			protected ?string $rootDir=null
		) {
			if(null === $rootDir) {
				$rootDir = getcwd();
			} elseif(DIRECTORY_SEPARATOR !== substr($rootDir, -1)) {
				$rootDir .= DIRECTORY_SEPARATOR;
			}
			
			$this->rootDir = $rootDir;
		}
		
		/**
		 * Convert a path relative to the root directory to a full path
		 * @param string $path Path relative to root directory
		 * @return string
		 */
		public function path(string $path): string {
			return $this->rootDir . ltrim($path, DIRECTORY_SEPARATOR);
		}
		
		/**
		 * Remove the leading root directory from a full path
		 * @param string $path Full path to strip the root path from
		 * @return string Path relative to root directory
		 */
		public function unrootPath(string $path): string {
			if(str_starts_with($path, $this->rootDir)) {
				$path = substr($path, strlen($this->rootDir));
			}
			
			return ltrim($path, DIRECTORY_SEPARATOR);
		}
	}