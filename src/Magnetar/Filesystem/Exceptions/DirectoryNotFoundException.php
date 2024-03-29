<?php
	declare(strict_types=1);
	
	namespace Magnetar\Filesystem\Exceptions;
	
	use Magnetar\Filesystem\Exceptions\DiskAdapterException;
	
	/**
	 * Exception thrown when a directory is not found
	 */
	class DirectoryNotFoundException extends DiskAdapterException {
		
	}