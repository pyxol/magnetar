<?php
	declare(strict_types=1);
	
	namespace Magnetar\Model;
	
	trait HasEvents {
		/**
		 * Observable event names for the model
		 * @var array
		 */
		protected array $observableEvents = [];
		
		/**
		 * Register an observer with the Model through the dispatcher
		 * @param array|string $classes
		 * @return void
		 */
		public static function observe(array|string $classes): void {
			// @TODO needs an Event/Dispatcher system
		}
	}