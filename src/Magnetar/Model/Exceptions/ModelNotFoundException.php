<?php
	declare(strict_types=1);
	
	namespace Magnetar\Model\Exceptions;
	
	use Exception;
	
	/**
	 * Exception thrown when a model could not be found by the specified identifer
	 */
	class ModelNotFoundException extends Exception {
		
	}