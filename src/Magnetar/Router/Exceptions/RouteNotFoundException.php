<?php
	declare(strict_types=1);
	
	namespace Magnetar\Router\Exceptions;
	
	use Exception;
	
	/**
	 * Exception thrown when the router cannot find a route matching the request
	 */
	class RouteNotFoundException extends Exception {
		
	}