<?php
	declare(strict_types=1);
	
	namespace Magnetar\Http\Exceptions;
	
	use Magnetar\Http\Exceptions\FileException;
	
	/**
	 * Called by UploadedFile::move() when the disk failed to move the uploaded file due to a disk write failure
	 */
	class DiskWriteException extends FileException {
		
	}