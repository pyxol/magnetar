<?php
	declare(strict_types=1);
	
	namespace Magnetar\Http\Controller;
	
	use Magnetar\Http\Controller\HasMiddlewareTrait;
	
	use BadMethodCallException;
	
	class Controller {
		use HasMiddlewareTrait;
		
		/**
		 * Call an action registered on the controller
		 * @param string $method The method to call
		 * @param array $args The arguments to pass to the method
		 * @return mixed
		 */
		public function callAction(string $method, array $args): mixed {
			return $this->{$method}(...$args);
		}
		
		/**
		 * Catch any undefined methods
		 * @param string $method
		 * @param array $args
		 * @return mixed
		 * 
		 * @throws \BadMethodCallException
		 */
		public function __call(string $method, array $args): mixed {
			throw new BadMethodCallException(sprintf(
				'The method %s does not exist for controller %s.',
				$method,
				static::class
			));
		}
	}