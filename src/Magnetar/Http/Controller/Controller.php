<?php
	declare(strict_types=1);
	
	namespace Magnetar\Http\Controller;
	
	use BadMethodCallException;
	
	class Controller {
		/**
		 * Catch any undefined methods
		 * @param string $method
		 * @param array $args
		 * @return void
		 * 
		 * @throws \BadMethodCallException
		 */
		public function __call(string $method, array $args): void {
			throw new BadMethodCallException(sprintf(
				"The method %s does not exist for controller %s.",
				$method,
				static::class
			));
		}
	}