<?php
	declare(strict_types=1);
	
	namespace Magnetar\Http\Exceptions;
	
	use Magnetar\Http\Exceptions\FileException;
	
	/**
	 * Called by UploadedFile::move() when there is no temporary folder available
	 */
	class TemporaryFolderMissingException extends FileException {
		
	}