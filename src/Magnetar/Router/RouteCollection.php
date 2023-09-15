<?php
	declare(strict_types=1);
	
	namespace Magnetar\Router;
	
	use Exception;
	
	use Magnetar\Router\Router;
	use Magnetar\Router\Enums\HTTPMethod;
	
	/**
	 * A collection for Route objects.
	 * 
	 * @todo 
	 */
	class RouteCollection {
		/**
		 * An array of routes within this collection
		 * @var array Route[]
		 */
		public array $routes = [];
		
		/**
		 * Constructor
		 * 
		 * @param Router $router The router instance
		 * @param string $pathPrefix The path prefix for all routes in this collection
		 * @param callable $callback The callback to run that will define the routes in this collection
		 * @param string|null $namePrefix The name prefix for all routes in this collection
		 * 
		 * @return self
		 * 
		 * @throws Exception If the callback throws an exception
		 */
		public function __construct(
			protected Router $router,
			protected RouteCollection|null $parentCollection = null,
			protected string $pathPrefix,
			callable $callback,
			protected string|null $namePrefix = null
		) {
			$this->attachContext();
			
			try {
				$callback();
			} catch(Exception $e) {
				// caught exception, rethrow (for now)
				
				throw $e;
			}
			
			$this->detachContext();
		}
		
		/**
		 * Attach this collection to the router's context
		 * @return void
		 */
		protected function attachContext(): void {
			$this->router->attachContext($this);
		}
		
		/**
		 * Detatch this collection from the router's context
		 * @return void
		 */
		protected function detachContext(): void {
			$this->router->detachContext();
		}
		
		/**
		 * Set the path prefix for all routes in this collection
		 * @param string $pathPrefix The path prefix
		 * @return self
		 */
		public function prefix(string $pathPrefix): self {
			$this->pathPrefix = $pathPrefix;
			
			return $this;
		}
		
		public function getNamePrefix(): string|null {
			return $this->namePrefix;
		}
		
		/**
		 * Generated a formatted name with the name prefix
		 * @param string $name The name of the route
		 * @return string The formatted name
		 */
		protected function formatNameWithPrefix(string $name): string {
			return $this->namePrefix . $name;
		}
		
		/**
		 * Generate a route, add it to the collection, and return it
		 * @param string $name The name of the route (prefix will be prepended)
		 * @param HTTPMethod|string $method The HTTP method to match against
		 * @param string $pattern The pattern to match against
		 * @return Route
		 */
		public function makeRoute(
			string $name,
			HTTPMethod|string $method,
			string $pattern
		): Route {
			// create and add the route to the collection
			$route_name = $this->formatNameWithPrefix($name);
			
			return $this->routes[ $route_name ] = new Route(
				$this,
				$route_name,
				$method,
				$pattern
			);
		}
	}