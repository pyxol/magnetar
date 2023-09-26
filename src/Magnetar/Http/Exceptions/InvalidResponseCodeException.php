<?php
	declare(strict_types=1);
	
	namespace Magnetar\Http\Exceptions;
	
	use Exception;
	
	/**
	 * Called by RedirectResponse when an invalid redirect code is set
	 */
	class InvalidResponseCodeException extends Exception {
		
	}