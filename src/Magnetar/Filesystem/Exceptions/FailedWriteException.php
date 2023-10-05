<?php
	declare(strict_types=1);
	
	namespace Magnetar\Filesystem\Exceptions;
	
	use Magnetar\Filesystem\Exceptions\DiskAdapterException;
	
	/**
	 * Exception thrown when the adapter fails to write a file
	 */
	class FailedWriteException extends DiskAdapterException {
		
	}