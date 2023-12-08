<?php
	declare(strict_types=1);
	
	namespace Magnetar\Http;
	
	/**
	 * Sorts a middleware stack by a specified priority
	 */
	class MiddlewareSorter {
		public function __construct(
			/**
			 * The middleware stack to sort
			 * @var array
			 */
			protected array $stack,
			
			/**
			 * The prioritized middleware stack to sort by. Array of namespaced class names
			 * @var array
			 */
			protected array $prioritizedStack=[]
		) {
			
		}
		
		/**
		 * Get the sorted list of middleware
		 * @return array
		 */
		public function sorted(): array {
			return $this->smoothMiddlewareStacks($this->stack, $this->prioritizedStack);
		}
		
		/**
		 * Smooth the middleware stack by replacing default prioritized middleware with extended versions, and appending any remaining middleware
		 * @param array $stack The middleware stack from the application
		 * @param array $prioritizedStack The prioritized middleware stack
		 * @return array The smoothed middleware stack
		 */
		public function smoothMiddlewareStacks(
			array $stack,
			array $prioritizedStack=[]
		): array {
			// start smoothed stack
			$smoothedStack = $prioritizedStack;
			
			// replace default with extended versions from stack, and append any remaining middleware
			foreach($stack as $class) {
				if(in_array($class, $smoothedStack)) {
					continue;
				}
				
				foreach($this->middlewareNames($class) as $name) {
					if(in_array($name, $smoothedStack)) {
						if($name !== $class) {
							$smoothedStack[ array_search($name, $smoothedStack) ] = $class;
						}
						
						continue 2;
					}
				}
				
				$smoothedStack[] = $class;
			}
			
			return $smoothedStack;
		}
		
		/**
		 * Get the names of the middleware class, it's interfaces, and parents
		 * @param string $class The middleware class
		 * @return \Generator
		 */
		protected function middlewareNames(string $class): \Generator {
			yield $class;
			
			$interfaces = @class_implements($class);
			
			if(false !== $interfaces) {
				foreach($interfaces as $interface) {
					yield $interface;
				}
			}
			
			$parents = @class_parents($class);
			
			if(false !== $parents) {
				foreach($parents as $parent) {
					yield $parent;
				}
			}
		}
	}