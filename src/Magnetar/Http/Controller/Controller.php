<?php
	declare(strict_types=1);
	
	namespace Magnetar\Http\Controller;
	
	use Magnetar\Kernel\Kernel;
	
	class Controller {
		protected Kernel $app;
		
		// @TODO needs more work
		public function __construct(Kernel $kernel) {
			// assign app
			$this->app = $kernel;
		}
		
		/**
		 * Catch any undefined methods
		 * @param string $method
		 * @param array $args
		 * @return void
		 */
		public function __call(string $method, array $args) {
			throw new BadMethodCallException(sprintf(
				"The method %s does not exist for controller %s.",
				$method,
				static::class
			));
		}
	}