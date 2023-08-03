<?php
	declare(strict_types=1);
	
	namespace Magnetar\Filesystem\Adapter;
	
	class Adapter {
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
		 * Get the root directory
		 * @return string
		 */
		public function rootDir(): string {
			return $this->rootDir;
		}
		
		/**
		 * Convert a path relative to the root directory to a full path
		 * @param string $path Path relative to root directory
		 * @return string Full path
		 */
		public function path(string $path): string {
			return $this->rootDir . ltrim($path, DIRECTORY_SEPARATOR);
		}
	}