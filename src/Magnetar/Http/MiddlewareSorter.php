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
			return $this->sortMiddleware($this->stack, $this->prioritizedStack);
		}
		
		/**
		 * Sort the middleware by priority
		 * @param array $stack The middleware to sort
		 * @param array $prioritizedStack The list of middleware and their priorities
		 * @return array
		 */
		protected function sortMiddleware(
			array $stack,
			array $prioritizedStack=[]
		): array {
			foreach($stack as $index => $class) {
				if(!is_string($class)) {
					continue;
				}
				
				$priorityIndex = $this->findPrioritizedIndex($class, $prioritizedStack);
				
				if(null !== $priorityIndex) {
					return $this->sortMiddleware(
						$prioritizedStack,
						array_values($this->moveMiddleware($stack, $index, $lastIndex))
					);
				}
				
				$lastIndex = $index;
				
				$lastPriorityIndex = $priorityIndex;
			}
			
			return $stack;
		}
		
		/**
		 * Find the index of the middleware in the prioritized stack
		 * @param string $class The middleware class
		 * @param array $prioritizedStack The prioritized stack
		 * @return int|null
		 */
		protected function findPrioritizedIndex(string $class, array $prioritizedStack): int|null {
			foreach($this->middlewareNames($class) as $name) {
				$index = array_search($name, $prioritizedStack);
				
				if($index !== false) {
					return $index;
				}
			}
			
			return null;
		}
		
		/**
		 * Get the names of the middleware class, it's interfaces, and parents
		 * @param string $class The middleware class
		 * @return \Generator
		 */
		protected function middlewareNames(string $class): \Generator {
			yield $class;
			
			$interfaces = @class_implements($class);
			
			if($interfaces !== false) {
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
		
		/**
		 * Move middleware from one index to another
		 * @param array $stack The middleware stack
		 * @param int $from The index to move the middleware from
		 * @param int $to The index to move the middleware to
		 * @return array
		 */
		protected function moveMiddleware(array $stack, int $from, int $to): array {
			array_splice($stack, $to, 0, $stack[ $from ]);
			
			unset($stack[ $from + 1 ]);
			
			return $stack;
		}
	}