<?php
	declare(strict_types=1);
	
	namespace Magnetar\Filesystem;
	
	/**
	 * Shapes a disk adapter to have the ability to manage files
	 */
	interface HasFilesInterface {
		/**
		 * Write contents to a file
		 * @param string $path File path
		 * @param string $contents Contents to write
		 * @param bool $overwrite Optional. Set to true to overwrite an existing file
		 * @return bool
		 * 
		 * @throws DestinationExistsException
		 * @throws FailedWriteException
		 */
		public function write(
			string $path,
			string $contents,
			bool $overwrite=false
		): bool;
		
		/**
		 * Copy a source file to a destination file. Throws error if source file does not exist or if destination file already exists and overwrite is not set to true
		 * @param string $source Source file path
		 * @param string $destination Destination file path
		 * @param bool $overwrite
		 * @return bool
		 * 
		 * @throws \Magnetar\Filesystem\Exceptions\SourceNotFoundException
		 * @throws \Magnetar\Filesystem\Exceptions\DestinationExistsException
		 */
		public function copy(
			string $source,
			string $destination,
			bool $overwrite=false
		): bool;
		
		/**
		 * Read the contents of a file
		 * @param string $path File path
		 * @return string|false The contents of the file, or false on failure
		 * 
		 * @throws \Magnetar\Filesystem\Exceptions\FileNotFoundException
		 */
		public function read(string $path): string|false;
		
		/**
		 * Determines if a path exists (directory or file)
		 * @param string $path Path to check
		 * @return bool True if path exists, false otherwise
		 */
		public function exists(string $path): bool;
		
		/**
		 * Determines if a path is a file
		 * @param string $path Path to check
		 * @return bool True if path is a file, false otherwise
		 */
		public function isFile(string $path): bool;
		
		/**
		 * Get the filename of a path
		 * @param string $path Path to check
		 * @return string The filename
		 */
		public function name(string $path): string;
		
		/**
		 * Get the basename of a path
		 * @param string $path Path to check
		 * @return string The basename
		 */
		public function basename(string $path): string;
		
		/**
		 * Get the extension of a path
		 * @param string $path Path to check
		 * @return string The extension
		 */
		public function extension(string $path): string;
		
		/**
		 * Get the file size of a path
		 * @param string $path
		 * @return int
		 */
		public function size(string $path): int;
		
		/**
		 * Detect the mimetype of a file
		 * @param string $path Path to check
		 * @return string|false The mimetype, or false on failure
		 */
		public function mimetype(string $path): string|false;
		
		/**
		 * Determine the timestamp of the last modification of a file
		 * @param string $path Path to check
		 * @return int|false The timestamp, or false on failure
		 */
		public function lastModified(string $path): string|false;
		
		/**
		 * Append contents to a file. If file does not exist, it will be created
		 * @param string $path Path relative to root directory
		 * @param string $contents Contents to append
		 * @return bool True on success, false on failure
		 */
		public function append(string $path, string $contents): bool;
		
		/**
		 * Prepend contents to a file. If file does not exist, it will be created
		 * @param string $path Path relative to root directory
		 * @param string $contents Contents to prepend
		 * @return bool True on success, false on failure
		 */
		public function prepend(string $path, string $contents): bool;
		
		/**
		 * Move a file
		 * @param string $source Source file path
		 * @param string $destination Destination file path
		 * @param bool $overwrite Optional. Set to true to overwrite an existing file
		 * @return bool True on success, false on failure
		 * 
		 * @throws FileNotFoundException
		 * @throws DestinationExistsException
		 * @throws FailedWriteException
		 */
		public function move(string $source, string $destination, bool $overwrite=false): bool;
		
		/**
		 * Delete file(s)
		 * @param string|array $paths Path(s) relative to root directory
		 * @return bool True on success, false on failure
		 */
		public function delete(string|array $paths): bool;
	}