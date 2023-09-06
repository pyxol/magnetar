<?php
	declare(strict_types=1);
	
	namespace Magnetar\Database\Exceptions;
	
	use Magnetar\Database\Exceptions\DatabaseAdapterException;
	
	/**
	 * Exception thrown when a query preperation error occurs
	 */
	class QueryPreperationException extends DatabaseAdapterException {
		
	}