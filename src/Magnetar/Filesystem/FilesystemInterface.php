<?php
	declare(strict_types=1);
	
	namespace Magnetar\Filesystem;
	
	/**
	 * Filesystem interface
	 */
	interface FilesystemInterface {
		// create
		public function write(string $path, string $contents, bool $overwrite=false): bool;
		public function copy(string $source, string $destination, bool $overwrite=false): bool;
		
		// read
		public function read(string $path): string|false;
		public function exists(string $path): bool;
		
		// update
		public function append(string $path, string $contents): bool;
		public function prepend(string $path, string $contents): bool;
		public function move(string $source, string $destination, bool $overwrite=false): bool;
		
		// delete
		public function delete(string|array $path): bool;
	}