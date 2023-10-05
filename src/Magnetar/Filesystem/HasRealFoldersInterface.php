<?php
	declare(strict_types=1);
	
	namespace Magnetar\Filesystem;
	
	use Magnetar\Filesystem\Exceptions\DiskAdapterException;
	
	/**
	 * Provides the disk adapter with the ability to manage directories
	 */
	interface HasRealFoldersInterface {
		/**
		 * Determines if a path exists (directory or file)
		 * @param string $path Path to check
		 * @return bool True if path exists, false otherwise
		 */
		public function directoryExists(string $path): bool;
		
		/**
		 * Determines if a path is a directory
		 * @param string $path Path to check
		 * @return bool True if path is a directory, false otherwise
		 */
		public function isDirectory(string $path): bool;
		
		/**
		 * Create a directory
		 * @param string $path Path relative to root directory
		 * @param int $mode Chmod permission. Defaults to 0777 (similar to mkdir())
		 * @param bool $recursive Whether to recursively create directories. Defaults to false (similar to mkdir())
		 * @return bool True on success, false on failure
		 */
		public function makeDirectory(string $path, int $mode=0777, bool $recursive=false): bool;
		
		/**
		 * Copy a directory and all of its contents to a destination directory. Throws error if source directory does not exist or if destination directory already exists. If ovewrite is set to true, the entirety of the existing destination directory will be deleted first
		 * @param string $source Source directory path
		 * @param string $destination Destination directory path
		 * @param bool $overwrite Optional. Set to true to overwrite an existing directory
		 * @return bool True on success, false on failure
		 * 
		 * @throws SourceNotFoundException
		 * @throws DestinationExistsException
		 */
		public function copyDirectory(string $source, string $destination, bool $overwrite=false): bool;
		
		/**
		 * Empty a directory but preserve the directory itself
		 * @param string $path Path relative to root directory
		 * @return bool True on success, false on failure
		 * 
		 * @throws DirectoryNotFoundException
		 */
		public function emptyDirectory(string $path): bool;
		
		/**
		 * Delete a directory and all of its contents
		 * @param string $path Path relative to root directory
		 * @return bool True on success, false on failure
		 * 
		 * @throws DirectoryNotFoundException
		 */
		public function deleteDirectory(string $path): bool;
	}