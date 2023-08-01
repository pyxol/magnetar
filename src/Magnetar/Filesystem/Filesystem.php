<?php
	declare(strict_types=1);
	
	namespace Magnetar\Filesystem;
	
	use ErrorException;
	use FilesystemIterator;
	
	use Magnetar\Filesystem\Adapter\FilesystemAdapter;
	use Magnetar\Filesystem\Exception\FileNotFoundException;
	use Magnetar\Filesystem\Exception\SourceNotFoundException;
	use Magnetar\Filesystem\Exception\DirectoryNotFoundException;
	use Magnetar\Filesystem\Exception\DestinationExistsException;
	
	/**
	 * A local filesystem implementation
	 * @package Magnetar\Filesystem
	 */
	class Filesystem implements FilesystemInterface {
		/**
		 * Filesystem constructor
		 * @param FilesystemAdapter $adapter
		 */
		public function __construct(
			protected FilesystemAdapter $adapter
		) {
			
		}
		
		/**
		 * Write contents to a file
		 * @param string $path File path
		 * @param string $contents Contents to write
		 * @param bool $overwrite Optional. Set to true to overwrite an existing file
		 * @return bool
		 * 
		 * @throws DestinationExistsException
		 */
		public function write(string $path, string $contents, bool $overwrite=false): bool {
			if(!$overwrite && $this->isFile($path)) {
				throw new DestinationExistsException("File already exists");
			}
			
			if(!$this->isDirectory(dirname($path))) {
				$this->makeDirectory(dirname($path));
			}
			
			return (false !== file_put_contents($this->adapter->path($path), $contents));
		}
		
		/**
		 * Copy a source file to a destination file. Throws error if source file does not exist or if destination file already exists and overwrite is not set to true
		 * @param string $path Source file path
		 * @param string $destination Destination file path
		 * @param bool $overwrite
		 * @return bool
		 * 
		 * @throws SourceNotFoundException
		 * @throws DestinationExistsException
		 */
		public function copy(string $path, string $destination, bool $overwrite=false): bool {
			if(!$this->isFile($path)) {
				throw new SourceNotFoundException("Source file does not exist");
			}
			
			if(!$overwrite && $this->isFile($destination)) {
				throw new DestinationExistsException("Destination file already exists");
			}
			
			return (false !== file_put_contents($this->adapter->path($destination), $this->read($path)));
		}
		
		/**
		 * Read the contents of a file
		 * @param string $path
		 * @return string|false
		 */
		public function read(string $path): string|false {
			return file_get_contents($this->adapter->path($path));
		}
		
		/**
		 * Determines if a path exists (directory or file)
		 * @param string $path
		 * @return bool
		 */
		public function exists(string $path): bool {
			return file_exists($this->adapter->path($path));
		}
		
		/**
		 * Determines if a path is a file
		 * @param string $path
		 * @return bool
		 */
		public function isFile(string $path): bool {
			return is_file($this->adapter->path($path));
		}
		
		/**
		 * Get the filename of a path
		 * @param string $path
		 * @return string
		 */
		public function name(string $path): string {
			return pathinfo($this->adapter->path($path), PATHINFO_FILENAME);
		}
		
		/**
		 * Get the basename of a path
		 * @param string $path
		 * @return string
		 */
		public function basename(string $path): string {
			return basename($this->adapter->path($path));
		}
		
		/**
		 * Get the extension of a path
		 * @param string $path
		 * @return string
		 */
		public function extension(string $path): string {
			return pathinfo($this->adapter->path($path), PATHINFO_EXTENSION);
		}
		
		/**
		 * Detect the mimetype of a file
		 * @param string $path
		 * @return string|false
		 */
		public function mimetype(string $path): string|false {
			return mime_content_type($this->adapter->path($path));
			//return finfo_file(finfo_open(FILEINFO_MIME_TYPE), $this->adapter->path($path));
		}
		
		/**
		 * Determine the timestamp of the last modification of a file
		 * @param string $path
		 * @return int|false
		 */
		public function lastModified(string $path): string|false {
			return filemtime($this->adapter->path($path));
		}
		
		/**
		 * Append contents to a file. If file does not exist, it will be created
		 * @param string $path
		 * @param string $contents
		 * @return bool
		 */
		public function append(string $path, string $contents): bool {
			return (false !== file_put_contents($this->adapter->path($path), $contents, FILE_APPEND));
		}
		
		/**
		 * Prepend contents to a file. If file does not exist, it will be created
		 * @param string $path
		 * @param string $contents
		 * @return bool
		 */
		public function prepend(string $path, string $contents): bool {
			if($this->isFile($path)) {
				return $this->write($path, $contents . $this->read($path), true);
			}
			
			return $this->write($path, $contents);
		}
		
		/**
		 * Move a file
		 * @param string $source
		 * @param string $destination
		 * @param bool $overwrite
		 * @return bool
		 * 
		 * @throws FileNotFoundException
		 * @throws DestinationExistsException
		 */
		public function move(string $source, string $destination, bool $overwrite=false): bool {
			if(!$this->isFile($source)) {
				throw new FileNotFoundException("Source file does not exist");
			}
			
			if(!$overwrite && $this->isFile($destination)) {
				throw new DestinationExistsException("Destination file already exists");
			}
			
			return (false !== file_put_contents($this->adapter->path($destination), $this->read($source)));
		}
		
		/**
		 * Delete file(s)
		 * @param string|array $paths
		 * @return bool
		 */
		public function delete(string|array $paths): bool {
			if(!is_array($paths)) {
				$paths = func_get_args();
			}
			
			$status = true;
			
			foreach($paths as $path) {
				try {
					$path = $this->adapter->path($path);
					
					if(@unlink($path)) {
						clearstatcache(true, $path);
					} else {
						$status = false;
					}
				} catch(ErrorException $e) {
					$status = false;
				}
			}
			
			return $status;
		}
		
		/**
		 * Determines if a path is a directory
		 * @param string $path
		 * @return bool
		 */
		public function isDirectory(string $path): bool {
			return is_dir($this->adapter->path($path));
		}
		
		/**
		 * Create a directory
		 * @param string $path Path relative to root directory
		 * @param int $mode Chmod permission. Defaults to 0777 (similar to mkdir())
		 * @param bool $recursive Whether to recursively create directories. Defaults to false (similar to mkdir())
		 * @return bool
		 */
		public function makeDirectory(string $path, int $mode=0777, bool $recursive=false): bool {
			if($this->isDirectory($path)) {
				return true;
			}
			
			return mkdir($this->adapter->path($path), $mode, $recursive);
		}
		
		/**
		 * Copy a directory and all of its contents to a destination directory. Throws error if source directory does not exist or if destination directory already exists. If ovewrite is set to true, the entirety of the existing destination directory will be deleted first
		 * 
		 * @param string $source
		 * @param string $destination
		 * @param bool $overwrite
		 * @return bool
		 * 
		 * @throws SourceNotFoundException
		 * @throws DestinationExistsException
		 */
		public function copyDirectory(string $source, string $destination, bool $overwrite=false): bool {
			$source = rtrim($source, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
			$destination = rtrim($destination, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
			
			if(!$this->isDirectory($source)) {
				throw new SourceNotFoundException("Source directory does not exist");
			}
			
			if($this->isDirectory($destination)) {
				if(!$overwrite) {
					throw new DestinationExistsException("Destination directory already exists");
				}
				
				$this->deleteDirectory($destination);
			}
			
			// ensure destination directory exists
			$this->makeDirectory($destination, 0777, true);
			
			// scan through directory, copy everything
			$items = new FilesystemIterator($this->adapter->path($source));
			
			foreach($items as $item) {
				if($item->isDir()) {
					// copy directory recursively
					$this->copyDirectory(
						$source . $item->getBasename(),
						$destination . $item->getBasename()
					);
				} else {
					$this->copy(
						$source . $item->getBasename(),
						$destination . $item->getBasename()
					);
				}
			}
			
			return true;
		}
		
		/**
		 * Empty a directory but preserve the directory itself
		 * @param string $path
		 * @return bool
		 * 
		 * @throws DirectoryNotFoundException
		 */
		public function emptyDirectory(string $path): bool {
			if(!$this->isDirectory($path)) {
				throw new DirectoryNotFoundException("Directory does not exist");
			}
			
			$path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
			
			// scan through directory, delete everything inside
			$items = new FilesystemIterator($this->adapter->path($path));
			
			foreach($items as $item) {
				if($item->isDir() && !$item->isLink()) {
					// delete sub-directory and contents
					$this->deleteDirectory(
						$path . $item->getBasename()
					);
				} else {
					$this->delete(
						$path . $item->getBasename()
					);
				}
			}
			
			return true;
		}
		
		/**
		 * Delete a directory and all of its contents
		 * @param string $path
		 * @return bool
		 * 
		 * @throws DirectoryNotFoundException
		 */
		public function deleteDirectory(string $path): bool {
			$this->emptyDirectory($path);
			
			@rmdir($this->adapter->path($path));
			
			return true;
		}
	}