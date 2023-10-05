<?php
	declare(strict_types=1);
	
	namespace Magnetar\Filesystem\Exceptions;
	
	use Magnetar\Filesystem\Exceptions\DiskAdapterException;
	
	/**
	 * Exception thrown when the drive isn't configured for public URLs
	 */
	class NoPublicEndpointException extends DiskAdapterException {
		
	}