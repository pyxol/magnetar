<?php
	declare(strict_types=1);
	
	namespace Magnetar\Router;
	
	use Magnetar\Router\Route;
	
	/**
	 * Registry that manages Route actions (callbacks)
	 */
	class RouteActionRegistry {
		/**
		 * An array of route actions (callbacks)
		 * @var array
		 */
		protected array $actions = [];
		
		/**
		 * Register a route action
		 * 
		 * @param string $name The name of the action
		 * @param callable|array|string|null $callback The callback to run when the action is called
		 * 
		 * @return Route
		 */
		public function register(
			Route $route,
			callable|array|string|null $callback
		): Route {
			$this->actions[ $route->getUniqueID() ] = $callback;
			
			return $route;
		}
		
		/**
		 * Get a route action
		 * 
		 * @param string $name The name of the action
		 * @param mixed $default The default value to return if the action is not found
		 * 
		 * @return mixed The callback for the action, or $default if not found
		 */
		public function get(
			string $name,
			mixed $default=null
		): mixed {
			return $this->actions[ $name ] ?? $default;
		}
		
		/**
		 * Check if a route action exists
		 * 
		 * @param string $name The name of the action
		 * 
		 * @return bool True if the action exists, false if not
		 */
		public function has(string $name): bool {
			return isset($this->actions[ $name ]);
		}
	}