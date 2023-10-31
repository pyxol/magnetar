<?php
	declare(strict_types=1);
	
	namespace Magnetar\Http\Exceptions;
	
	use Magnetar\Http\Exceptions\FileException;
	
	/**
	 * Called by UploadedFile::move() when the uploaded file was only uploaded partially
	 */
	class PartialUploadedFileException extends FileException {
		
	}