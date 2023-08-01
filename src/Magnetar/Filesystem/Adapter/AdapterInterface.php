<?php
	declare(strict_types=1);
	
	namespace Magnetar\Filesystem\Adapter;
	
	use Magnetar\Filesystem\Exception\DestinationExistsException;
	
	interface AdapterInterface {
		/**
		 * Convert a path relative to the root directory to a full path
		 * @param string $path Path relative to root directory
		 * @return string Full path
		 */
		public function path(string $path): string;
	}