<?php
	declare(strict_types=1);
	
	namespace Magnetar\Http\Exceptions;
	
	use Magnetar\Http\Exceptions\FileException;
	
	/**
	 * Called by UploadedFile::move() when an extension stopped the file upload
	 */
	class ExtensionStoppedUploadException extends FileException {
		
	}