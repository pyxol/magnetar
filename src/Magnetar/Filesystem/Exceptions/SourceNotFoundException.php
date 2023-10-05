<?php
	declare(strict_types=1);
	
	namespace Magnetar\Filesystem\Exceptions;
	
	use Magnetar\Filesystem\Exceptions\DiskAdapterException;
	
	/**
	 * Exception thrown when a source is not found
	 */
	class SourceNotFoundException extends DiskAdapterException {
		
	}