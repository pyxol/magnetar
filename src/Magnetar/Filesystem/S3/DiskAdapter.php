<?php
	declare(strict_types=1);
	
	namespace Magnetar\Filesystem\S3;
	
	use Exception;
	use ErrorException;
	
	use Magnetar\Filesystem\DiskAdapter as BaseDiskAdapter;
	use Magnetar\Filesystem\HasFilesInterface;
	use Magnetar\Filesystem\HasPublicURLsInterface;
	use Magnetar\Filesystem\S3\S3Filesystem;
	use Magnetar\Filesystem\Exceptions\SourceNotFoundException;
	use Magnetar\Filesystem\Exceptions\DestinationExistsException;
	use Magnetar\Filesystem\Exceptions\FileNotFoundException;
	use Magnetar\Filesystem\Exceptions\FailedWriteException;
	
	class DiskAdapter extends BaseDiskAdapter implements HasFilesInterface, HasPublicURLsInterface {
		/**
		 * {@inheritDoc}
		 */
		const ADAPTER_NAME = 'disk';
		
		/**
		 * Handler for the S3 filesystem
		 * @var S3Filesystem
		 */
		protected S3Filesystem $s3;
		
		/**
		 * Root path
		 * @var string
		 */
		protected string $rootPath = '';
		
		/**
		 * {@inheritDoc}
		 */
		protected function validateRuntime(): void {
			parent::validateRuntime();
			
			// use S3Filesystem to validate the config
			S3Filesystem::validateConfig($this->connection_config);
		}
		
		/**
		 * {@inheritDoc}
		 */
		protected function createConnection(): void {
			// create the S3 filesystem handler
			$this->s3 = new S3Filesystem(
				$this->connection_config
			);
		}
		
		/**
		 * {@inheritDoc}
		 */
		public function path(string $rel_path=''): string {
			return $this->rootPath . ltrim($rel_path, '/');
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
				throw new DestinationExistsException("File already exists");
			}
			
			try {
				$this->s3->write($path, $contents);
				
				return true;
			} catch(Exception $e) {
				throw new FailedWriteException($e->getMessage());
			}
		}
		
		/**
		 * {@inheritDoc}
		 */
		public function copy(
			string $source,
			string $destination,
			bool $overwrite=false
		): bool {
			try {
				if(!$this->isFile($source)) {
					throw new SourceNotFoundException("Source file does not exist");
				}
				
				if(!$overwrite && $this->isFile($destination)) {
					throw new DestinationExistsException("Destination file already exists");
				}
				
				return $this->s3->copy(
					$this->path($source),
					$this->path($destination)
				);
			} catch(Exception $e) {
				return false;
			}
		}
		
		/**
		 * {@inheritDoc}
		 */
		public function read(string $path): string|false {
			try {
				return $this->s3->read($this->path($path));
			} catch(Exception $e) {
				throw new FileNotFoundException($e->getMessage());
			}
		}
		
		/**
		 * {@inheritDoc}
		 */
		public function exists(string $path): bool {
			try {
				return $this->s3->fileExists($this->path($path));
			} catch(Exception $e) {
				return false;
			}
		}
		
		/**
		 * {@inheritDoc}
		 */
		public function isFile(string $path): bool {
			return $this->exists($path);
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
			try {
				return $this->s3->fileSize($this->path($path));
			} catch(Exception $e) {
				return false;
			}
		}
		
		/**
		 * {@inheritDoc}
		 */
		public function mimetype(string $path): string|false {
			try {
				return $this->s3->mimeType($this->path($path));
			} catch(Exception $e) {
				return false;
			}
		}
		
		/**
		 * {@inheritDoc}
		 */
		public function lastModified(string $path): string|false {
			try {
				return $this->s3->lastModified($this->path($path));
			} catch(Exception $e) {
				return false;
			}
		}
		
		/**
		 * {@inheritDoc}
		 */
		public function append(string $path, string $contents): bool {
			if($this->isFile($path)) {
				return $this->write($path, $this->read($path) . $contents, true);
			}
			
			return $this->write($path, $contents);
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
			try {
				if(!$this->isFile($source)) {
					throw new SourceNotFoundException("Source file does not exist");
				}
				
				if(!$overwrite && $this->isFile($destination)) {
					throw new DestinationExistsException("Destination file already exists");
				}
				
				return $this->s3->move(
					$this->path($source),
					$this->path($destination)
				);
			} catch(Exception $e) {
				throw new FailedWriteException($e->getMessage());
			}
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
					$this->s3->delete($this->path($path));
				} catch(ErrorException $e) {
					$status = false;
				}
			}
			
			return $status;
		}
		
		/**
		 * {@inheritDoc}
		 */
		public function url(string $rel_path): string {
			return $this->s3->publicUrl($this->path($rel_path));
		}
	}