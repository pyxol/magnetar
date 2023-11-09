<?php
	declare(strict_types=1);
	
	namespace Magnetar\Auth\Exceptions;
	
	use Exception;
	
	/**
	 * Exception thrown when certain methods are called after authentication adapter initialization
	 */
	class AlreadyInitializedException extends Exception {
		
	}