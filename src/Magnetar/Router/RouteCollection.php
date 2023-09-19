<?php
	declare(strict_types=1);
	
	namespace Magnetar\Router;
	
	use Exception;
	
	use Magnetar\Router\Router;
	use Magnetar\Router\Route;
	use Magnetar\Router\Enums\HTTPMethodEnum;
	
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
		 * The name prefix for all routes in this collection
		 * @var string|null The name prefix
		 */
		protected string|null $namePrefix = null;
		
		/**
		 * Constructor
		 * 
		 * @param Router $router The router instance
		 * @param string $pathPrefix The path prefix for all routes in this collection
		 * @param callable|null $callback The callback to run that will define the routes in this collection
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
			callable|null $callback=null
		) {
			// process callback?
			if(null !== $callback) {
				$this->processCallback($callback);
			}
		}
		
		/**
		 * Process the constructor callback (if set)
		 * @param callable $callback The callback to run
		 * @return void
		 */
		protected function processCallback(callable $callback): void {
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
		
		/**
		 * Set the name prefix for all routes in this collection
		 * @param string|null $namePrefix The name prefix
		 * @return self
		 */
		public function namePrefix(string|null $namePrefix): self {
			$this->namePrefix = $namePrefix;
			
			return $this;
		}
		
		/**
		 * Get the path prefix for all routes in this collection
		 * @return string The path prefix
		 */
		public function getNamePrefix(): string|null {
			return $this->namePrefix;
		}
		
		/**
		 * Generated a formatted name with the name prefix
		 * @param string $name The name of the route
		 * @return string The formatted name
		 */
		public function formatNameWithPrefix(string $name): string {
			return $this->namePrefix . $name;
		}
		
		/**
		 * Generate a route, add it to the collection, and return it
		 * @param string $pattern The pattern to match against
		 * @param HTTPMethodEnum|string|null $method The HTTP method to match against. If null, all methods are matched
		 * @return Route
		 */
		public function makeRoute(
			HTTPMethodEnum|string|null $method=null,
			string $pattern
		): Route {
			// create and add the route to the collection
			// assign route and return it
			return $this->routes[] = new Route(
				$this,
				$method,
				$pattern
			);
		}
		
		/**
		 * Add a route to the collection
		 * @param Route $route The route to add
		 * @return Route This collection
		 */
		public function add(
			Route $route
		): Route {
			return $this->routes[] = $route;
		}
	}