<?php
	declare(strict_types=1);
	
	namespace Magnetar\Filesystem\Disk;
	
	use ErrorException;
	use FilesystemIterator;
	
	use Magnetar\Filesystem\DiskAdapter as BaseDiskAdapter;
	use Magnetar\Filesystem\HasFilesInterface;
	use Magnetar\Filesystem\HasRealFoldersInterface;
	use Magnetar\Filesystem\HasPublicURLsInterface;
	use Magnetar\Filesystem\Exceptions\DiskAdapterException;
	use Magnetar\Filesystem\Exceptions\SourceNotFoundException;
	use Magnetar\Filesystem\Exceptions\DestinationExistsException;
	use Magnetar\Filesystem\Exceptions\DirectoryNotFoundException;
	use Magnetar\Filesystem\Exceptions\FileNotFoundException;
	use Magnetar\Filesystem\Exceptions\NoPublicEndpointException;
	
	class DiskAdapter extends BaseDiskAdapter implements HasFilesInterface, HasRealFoldersInterface, HasPublicURLsInterface {
		/**
		 * {@inheritDoc}
		 */
		const ADAPTER_NAME = 'disk';
		
		/**
		 * Root path
		 * @var string
		 */
		protected string $rootPath = '';
		
		/**
		 * Public path URI
		 * @var string
		 */
		protected string|bool $publicPath = false;
		
		/**
		 * Get the full path to a file relative to the root directory
		 * @param string $rel_path Path relative to root directory. Defaults to empty string which returns the root path
		 * @return string Full path
		 */
		public function path(string $rel_path=''): string {
			return $this->rootPath . ltrim($rel_path, DIRECTORY_SEPARATOR);
		}
		
		/**
		 * {@inheritDoc}
		 */
		protected function validateRuntime(): void {
			parent::validateRuntime();
			
			// check if the connection configuration has a root path
			if(!isset($this->connection_config['root'])) {
				throw new DiskAdapterException('Invalid connection root path');
			}
		}
		
		/**
		 * {@inheritDoc}
		 */
		protected function createConnection(): void {
			// set the root path
			$this->rootPath = $this->connection_config['root'];
			
			// set the public path
			$this->publicPath = (isset($this->connection_config['url'])?rtrim($this->connection_config['url'], '/'):false);
		}
		
		/**
		 * {@inheritDoc}
		 */
		public function write(
			string $path,
			string $contents,
			bool $overwrite=false
		): bool {
			if(!$overwrite && $this->isFile($path)) {
				throw new DestinationExistsException('File already exists');
			}
			
			if(!$this->isDirectory(dirname($path))) {
				$this->makeDirectory(dirname($path));
			}
			
			return (false !== file_put_contents($this->path($path), $contents));
		}
		
		/**
		 * {@inheritDoc}
		 */
		public function copy(
			string $source,
			string $destination,
			bool $overwrite=false
		): bool {
			if(!$this->isFile($source)) {
				throw new SourceNotFoundException('Source file does not exist');
			}
			
			if(!$overwrite && $this->isFile($destination)) {
				throw new DestinationExistsException('Destination file already exists');
			}
			
			return (false !== file_put_contents($this->path($destination), $this->read($source)));
		}
		
		/**
		 * {@inheritDoc}
		 */
		public function read(string $path): string|false {
			return file_get_contents($this->path($path));
		}
		
		/**
		 * {@inheritDoc}
		 */
		public function exists(string $path): bool {
			return file_exists($this->path($path));
		}
		
		/**
		 * {@inheritDoc}
		 */
		public function isFile(string $path): bool {
			return is_file($this->path($path));
		}
		
		/**
		 * {@inheritDoc}
		 */
		public function name(string $path): string {
			return pathinfo($this->path($path), PATHINFO_FILENAME);
		}
		
		/**
		 * {@inheritDoc}
		 */
		public function basename(string $path): string {
			return basename($this->path($path));
		}
		
		/**
		 * {@inheritDoc}
		 */
		public function extension(string $path): string {
			return pathinfo($this->path($path), PATHINFO_EXTENSION);
		}
		
		/**
		 * {@inheritDoc}
		 */
		public function size(string $path): int {
			return filesize($this->path($path));
		}
		
		/**
		 * {@inheritDoc}
		 */
		public function mimetype(string $path): string|false {
			return mime_content_type($this->path($path));
			//return finfo_file(finfo_open(FILEINFO_MIME_TYPE), $this->path($path));
		}
		
		/**
		 * {@inheritDoc}
		 */
		public function lastModified(string $path): string|false {
			return filemtime($this->path($path));
		}
		
		/**
		 * {@inheritDoc}
		 */
		public function append(string $path, string $contents): bool {
			return (false !== file_put_contents($this->path($path), $contents, FILE_APPEND));
		}
		
		/**
		 * {@inheritDoc}
		 */
		public function prepend(string $path, string $contents): bool {
			if($this->isFile($path)) {
				return $this->write($path, $contents . $this->read($path), true);
			}
			
			return $this->write($path, $contents);
		}
		
		/**
		 * {@inheritDoc}
		 */
		public function move(string $source, string $destination, bool $overwrite=false): bool {
			if(!$this->isFile($source)) {
				throw new FileNotFoundException('Source file does not exist');
			}
			
			if(!$overwrite && $this->isFile($destination)) {
				throw new DestinationExistsException('Destination file already exists');
			}
			
			return (false !== file_put_contents($this->path($destination), $this->read($source)));
		}
		
		/**
		 * {@inheritDoc}
		 */
		public function delete(string|array $paths): bool {
			if(!is_array($paths)) {
				$paths = func_get_args();
			}
			
			$status = true;
			
			foreach($paths as $path) {
				try {
					$path = $this->path($path);
					
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
		 * {@inheritDoc}
		 */
		public function directoryExists(string $path): bool {
			return file_exists($this->path($path));
		}
		
		/**
		 * {@inheritDoc}
		 */
		public function isDirectory(string $path): bool {
			return is_dir($this->path($path));
		}
		
		/**
		 * {@inheritDoc}
		 */
		public function makeDirectory(string $path, int $mode=0777, bool $recursive=false): bool {
			if($this->isDirectory($path)) {
				return true;
			}
			
			return mkdir($this->path($path), $mode, $recursive);
		}
		
		/**
		 * {@inheritDoc}
		 */
		public function copyDirectory(string $source, string $destination, bool $overwrite=false): bool {
			$source = rtrim($source, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
			$destination = rtrim($destination, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
			
			if(!$this->isDirectory($source)) {
				throw new SourceNotFoundException('Source directory does not exist');
			}
			
			if($this->isDirectory($destination)) {
				if(!$overwrite) {
					throw new DestinationExistsException('Destination directory already exists');
				}
				
				$this->deleteDirectory($destination);
			}
			
			// ensure destination directory exists
			$this->makeDirectory($destination, 0777, true);
			
			// scan through directory, copy everything
			$items = new FilesystemIterator($this->path($source));
			
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
		 * {@inheritDoc}
		 */
		public function emptyDirectory(string $path): bool {
			if(!$this->isDirectory($path)) {
				throw new DirectoryNotFoundException('Directory does not exist');
			}
			
			$path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
			
			// scan through directory, delete everything inside
			$items = new FilesystemIterator($this->path($path));
			
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
		 * {@inheritDoc}
		 */
		public function deleteDirectory(string $path): bool {
			$this->emptyDirectory($path);
			
			@rmdir($this->path($path));
			
			return true;
		}
		
		
		/**
		 * {@inheritDoc}
		 */
		public function url(string $path): string {
			if(false === $this->publicPath) {
				throw new NoPublicEndpointException('Public path not set');
			}
			
			return $this->publicPath .'/'. ltrim($path, '/');
		}
	}