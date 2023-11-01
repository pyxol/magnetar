<?php
	declare(strict_types=1);
	
	namespace Magnetar\Router;
	
	use Exception;
	use Magnetar\Http\Request;
	use Magnetar\Router\Router;
	use Magnetar\Router\Route;
	use Magnetar\Router\Enums\HTTPMethodEnum;

	/**
	 * A collection manager for Route objects. Subsequent instances are typically shortlived and allow for grouping routes together
	 */
	class RouteCollection {
		/**
		 * An array of routes within this collection
		 * @var array Route[]
		 */
		public array $routes = [];
		
		/**
		 * The name prefix for all routes in this collection
		 * @var string The name prefix
		 */
		protected string $namePrefix = '';
		
		/**
		 * Constructor
		 * 
		 * @param Router $router The router instance
		 * @param RouteCollection|null $parentCollection The parent collection (if this collection is a sub-collection)
		 * @param string $pathPrefix The path prefix for all routes in this collection
		 * @param callable|null $callback The callback to run that will define the routes in this collection
		 * 
		 * @return self
		 * 
		 * @throws Exception If the callback throws an exception
		 */
		public function __construct(
			protected Router $router,
			protected RouteCollection|null $parentCollection = null,
			protected string $pathPrefix = '',
			callable|null $callback = null
		) {
			if(null !== $this->parentCollection) {
				$this->pathPrefix = $this->parentCollection->formatPathWithPrefix($this->pathPrefix);
			}
			
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
			// provide the parent collection with the routes generated in this collection
			if(null !== $this->parentCollection) {
				$this->parentCollection->addRoutes($this->routes);
			}
			
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
		 * Format a path with the path prefix. Returns path with no leading or trailing slashes
		 * @param string $path
		 * @return string
		 */
		public function formatPathWithPrefix(string $path): string {
			return trim(rtrim($this->pathPrefix, '/') .'/'. ltrim($path, '/'), '/');
		}
		
		/**
		 * Set the name prefix for all routes in this collection
		 * @param string $namePrefix The name prefix
		 * @return self
		 */
		public function namePrefix(string $namePrefix=''): self {
			if(null !== $this->parentCollection) {
				$namePrefix = $this->parentCollection->formatNameWithPrefix($namePrefix);
			}
			
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
		 * @param HTTPMethodEnum|string|null $method The HTTP method to match against. If null, all methods are matched
		 * @param string $pattern The pattern to match against
		 * @return Route
		 */
		public function makeRoute(
			HTTPMethodEnum|array|string|null $method=null,
			string $pattern
		): Route {
			// create and add the route to the collection
			// assign route and return it
			return (new Route(
				$this,
				$method,
				$pattern
			));
		}
		
		/**
		 * Add a route to the collection
		 * @param Route $route The route to add
		 * @return Route This collection
		 */
		public function add(
			Route $route
		): Route {
			return $this->routes[ $route->getName() ] = $route;
		}
		
		/**
		 * Add multiple routes to the collection
		 * @param array $routes Route instances to add
		 * @return self
		 */
		public function addRoutes(
			array $routes
		): self {
			//$this->routes = array_merge($this->routes, $routes);
			foreach($routes as $route) {
				$this->add($route);
			}
			
			return $this;
		}
		
		/**
		 * Attempt to match a request to a route
		 * @param Request $request
		 * @return Route|null
		 */
		public function matchRequestToRoute(
			HTTPMethodEnum $method,
			string $path
		): Route|null {
			// loop through routes
			foreach($this->routes as $route) {
				// check if the route matches the request
				if($route->matches($method, $path)) {
					// route matches, return it
					return $route;
				}
			}
			
			// no route matches, return null
			return null;
		}
	}