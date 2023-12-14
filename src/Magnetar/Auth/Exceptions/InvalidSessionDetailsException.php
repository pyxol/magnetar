<?php
	declare(strict_types=1);
	
	namespace Magnetar\Auth\Exceptions;
	
	class InvalidSessionDetailsException extends Exception {
		/**
		 * The exception message
		 * @var string
		 */
		protected string $message = 'Invalid session details';
	}