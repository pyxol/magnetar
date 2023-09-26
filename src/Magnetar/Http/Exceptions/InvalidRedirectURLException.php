<?php
	declare(strict_types=1);
	
	namespace Magnetar\Http\Exceptions;
	
	use Exception;
	
	/**
	 * Called by RedirectResponse::send() when no URL has been set
	 */
	class InvalidRedirectURLException extends Exception {
		
	}