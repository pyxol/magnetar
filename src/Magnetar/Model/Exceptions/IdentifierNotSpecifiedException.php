<?php
	declare(strict_types=1);
	
	namespace Magnetar\Model\Exceptions;
	
	use Exception;
	
	/**
	 * Exception thrown when a model attempts to save updates without an identifier
	 */
	class IdentifierNotSpecifiedException extends Exception {
		
	}