<?php
	declare(strict_types=1);
	
	namespace Magnetar\Http\Exceptions;
	
	use Magnetar\Http\Exceptions\FileException;
	
	/**
	 * Called by UploadedFile::move() when the file size is higher than the post limit set in php.ini
	 */
	class UploadedPostSizeLimitException extends FileException {
		
	}