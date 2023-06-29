<?php
	declare(strict_types=1);
	
	namespace Magnetar\Filesystem\Adapter;
	
	class FilesystemAdapter implements AdapterInterface {
		protected string $rootDir = '/';
		
		public function __construct(
			string $rootDir='/'
		) {
			if(DIRECTORY_SEPARATOR !== substr($rootDir, -1)) {
				$rootDir .= DIRECTORY_SEPARATOR;
			}
			
			$this->rootDir = $rootDir;
		}
		
		/**
		 * Get the full path to a file
		 * @param string $path Path to file relative to root directory
		 * @return string
		 */
		public function path(string $path): string {
			return $this->rootDir . ltrim($path, DIRECTORY_SEPARATOR);
		}
	}