<?php
	declare(strict_types=1);
	
	namespace Magnetar\Http;
	
	use Exception;
	use Magnetar\Http\Exceptions\FileException;
	use Magnetar\Http\Exceptions\MoveUploadedFileException;
	use Magnetar\Http\Exceptions\UploadedFileSizeLimitException;
	use Magnetar\Http\Exceptions\UploadedPostSizeLimitException;
	use Magnetar\Http\Exceptions\PartialUploadedFileException;
	use Magnetar\Http\Exceptions\NoFileFoundException;
	use Magnetar\Http\Exceptions\TemporaryFolderMissingException;
	use Magnetar\Http\Exceptions\DiskWriteException;
	use Magnetar\Http\Exceptions\ExtensionStoppedUploadException;
	
	class UploadedFile {
		/**
		 * If true, the file has been moved to a new location
		 * @var bool
		 */
		protected bool $moved = false;
		
		/**
		 * Create a new uploaded file instance
		 * @param string $path The path to the uploaded file
		 * @param string $originalName The original file name
		 * @param string $mimeType The type of the uploaded file
		 * @param int $size The size of the uploaded file in bytes
		 * @param int $error The error associated with the uploaded file
		 */
		public function __construct(
			protected string $path,
			protected string $originalName,
			protected string $mimeType,
			protected int $size,
			protected int $error
		) {
			
		}
		
		/**
		 * Get the path to the uploaded file's temporary location
		 * @return string
		 */
		public function getPath(): string {
			return $this->path;
		}
		
		/**
		 * Get the original file name
		 * @return string
		 */
		public function getClientOriginalName(): string {
			return $this->originalName;
		}
		
		/**
		 * Get the mime type of the uploaded file
		 * @return string
		 */
		public function getClientMimeType(): string {
			return $this->mimeType;
		}
		
		/**
		 * Get the file size of the uploaded file
		 * @return int
		 */
		public function getClientSize(): int {
			return $this->size;
		}
		
		/**
		 * Determine if the uploaded file is considered valid (did not trigger any upload errors)
		 * @return bool
		 */
		public function isValid(): bool {
			return (\UPLOAD_ERR_OK === $this->error) && is_uploaded_file($this->path);
		}
		
		/**
		 * Get the error code associated with the uploaded file
		 * @return int
		 */
		public function getError(): int {
			return $this->error;
		}
		
		/**
		 * Generate a human-readable error message for the given error code
		 * @return string
		 */
		public function getErrorMessage(): string {
			switch($this->error) {
				case \UPLOAD_ERR_INI_SIZE:
					return 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
				
				case \UPLOAD_ERR_FORM_SIZE:
					return 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
				
				case \UPLOAD_ERR_PARTIAL:
					return 'The uploaded file was only partially uploaded';
				
				case \UPLOAD_ERR_NO_FILE:
					return 'No file was uploaded';
				
				case \UPLOAD_ERR_NO_TMP_DIR:
					return 'Missing a temporary folder';
				
				case \UPLOAD_ERR_CANT_WRITE:
					return 'Failed to write file to disk';
				
				case \UPLOAD_ERR_EXTENSION:
					return 'A PHP extension stopped the file upload';
			}
			
			return 'An unknown error occurred';
		}
		
		/**
		 * Determine if the uploaded file has already been moved to a new location
		 * @return bool
		 */
		public function isMoved(): bool {
			return $this->moved;
		}
		
		/**
		 * Get the target path of the uploaded file
		 * @param string $directory The directory to move to
		 * @param string $name The new file name
		 * @return string
		 */
		public function getTargetPath(string $directory, string $name): string {
			if(!is_dir($directory)) {
				if(false === mkdir($directory, 0777, true)) {
					throw new Exception(sprintf('Unable to create the "%s" directory', $directory));
				}
			} elseif(!is_writable($directory)) {
				throw new Exception(sprintf('Unable to write in the "%s" directory', $directory));
			}
			
			return rtrim($directory, '/\\') . \DIRECTORY_SEPARATOR . $name;
		}
		
		/**
		 * Move the uploaded file to a new location
		 * @param string $targetPath The path to move to
		 * @return bool
		 * 
		 * @throws UploadedFileSizeLimitException
		 * @throws UploadedPostSizeLimitException
		 * @throws PartialUploadedFileException
		 * @throws NoFileFoundException
		 * @throws TemporaryFolderMissingException
		 * @throws DiskWriteException
		 * @throws ExtensionStoppedUploadException
		 * @throws FileException
		 * @throws MoveUploadedFileException
		 */
		public function move(string $targetDirectory, string $targetName): bool {
			if($this->isValid()) {
				if($this->isMoved()) {
					return false;
				}
				
				$targetPath = $this->getTargetPath($targetDirectory, $targetName);
				
				// trap error handler
				set_error_handler(function ($type, $msg) use (&$error) { $error = $msg; });
				
				// attempt to move
				try {
					$this->moved = move_uploaded_file($this->path, $targetPath);
				} finally {
					restore_error_handler();
				}
				
				// move the file to the target path
				if(!$this->moved) {
					throw new MoveUploadedFileException(
						sprintf(
							'Could not move uploaded file "%s" to "%s": %s',
							$this->getClientOriginalName(),
							$targetPath,
							strip_tags($error)
						)
					);
				}
				
				@chmod($targetPath, 0666 & ~umask());
				
				return true;
			}
			
			switch($this->error) {
				case \UPLOAD_ERR_INI_SIZE:
					throw new Exceptions\UploadedFileSizeLimitException( $this->getErrorMessage() );
				
				case \UPLOAD_ERR_FORM_SIZE:
					throw new Exceptions\UploadedPostSizeLimitException( $this->getErrorMessage() );
				
				case \UPLOAD_ERR_PARTIAL:
					throw new Exceptions\PartialUploadedFileException( $this->getErrorMessage() );
				
				case \UPLOAD_ERR_NO_FILE:
					throw new Exceptions\NoFileFoundException( $this->getErrorMessage() );
				
				case \UPLOAD_ERR_NO_TMP_DIR:
					throw new Exceptions\TemporaryFolderMissingException( $this->getErrorMessage() );
				
				case \UPLOAD_ERR_CANT_WRITE:
					throw new Exceptions\DiskWriteException( $this->getErrorMessage() );
				
				case \UPLOAD_ERR_EXTENSION:
					throw new Exceptions\ExtensionStoppedUploadException( $this->getErrorMessage() );
			}
			
			throw new FileException( $this->getErrorMessage() );
		}
	}