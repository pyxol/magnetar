<?php
	declare(strict_types=1);
	
	namespace Magnetar\Http\Exceptions;
	
	use Magnetar\Http\Exceptions\FileException;
	
	/**
	 * Called by UploadedFile::move() when the server is unable to move the uploded file
	 */
	class MoveUploadedFileException extends FileException {
		
	}