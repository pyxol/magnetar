<?php
	declare(strict_types=1);
	
	namespace Magnetar\Router;
	
	use RuntimeException;   // @TMP
	
	use Magnetar\Container\Container;
	use Magnetar\Http\Request;
	use Magnetar\Http\Response;
	use Magnetar\Router\RouteCollection;
	use Magnetar\Router\Enums\HTTPMethodEnum;
	use Magnetar\Router\Exceptions\RouteUnassignedException;
	use Magnetar\Router\Exceptions\CannotProcessRouteException;
	
	/**
	 * Router class
	 * 
	 * @todo ->group() isn't finished yet
	 * @todo grouped collections' routes aren't being added to the router
	 * @todo create __call()
	 * @todo move get/post/put/etc. methods to RouteCollection, define in __call()
	 */
	class Router {
		/**
		 * Whether or not the request has been served
		 * @var bool
		 */
		protected bool $served = false;
		
		/**
		 * The request method
		 * @var string|null
		 */
		protected ?string $requestMethod = null;
		
		/**
		 * Array of routes to match against. Key is the regex pattern to match against request uri, value is the callback to run if matched
		 * @var array
		 */
		protected array $routeCallbacks = [];
		
		/**
		 * The current route collection
		 * @var RouteCollection
		 */
		protected RouteCollection $routeCollection;
		
		/**
		 * Contextual array of route collections.
		 * The first entry is the primary route collection. Subsequent entries are contextually relevant.
		 * Used when grouping routes inside of a route collection's initialization callback
		 * @var array<RouteCollection>
		 */
		protected array $contextualRouteCollections = [];
		
		/**
		 * Constructor
		 * @param Container $container The container instance
		 * @param string $pathPrefix The path's prefix
		 * @return void
		 */
		public function __construct(
			/**
			 * The container instance
			 * @var Container
			 */
			protected Container $container,
			
			/**
			 * The path's prefix
			 * @var string
			 */
			protected string $pathPrefix=''
		) {
			$this->requestMethod = $_SERVER['REQUEST_METHOD'] ?? null;
			
			// create the base route collection
			$this->routeCollection = new RouteCollection(
				$this,
				null,
				$this->pathPrefix
			);
		}
		
		/**
		 * Turn the request into a response by matching against the registered routes
		 * @param Request $request The request
		 * @return Response The response
		 * 
		 * @throws CannotProcessRouteException
		 * @throws RouteUnassignedException
		 */
		public function processRequest(Request $request): Response {
			//throw new RuntimeException("Router::processRequest() not yet fully implemented, routes are not read from APP/routing/website.php");
			
			// run through registered routes, find a match, pass execute callback to response class
			foreach($this->routeCallbacks as $pattern => $callback) {
				// @TODO attemptPathPattern() should use $request
				if(!$this->attemptPathPattern($pattern)) {
					continue;
				}
				
				$this->served = true;
				
				// execute callback
				if(is_array($callback)) {
					// class reference and method
					list($instance, $method) = $callback;
					
					if(is_string($instance)) {
						$instance = new ($instance)($this);
					}
					
					//$instance->$method($this->request, $this->response);
					
					// call instance method and reference params
					//call_user_func([$instance, $method]);
					return $this->container->instance('response', call_user_func([$instance, $method]));
				} elseif(is_callable($callback)) {
					// closure
					//call_user_func_array($callback, $params);
					//call_user_func($callback);
					return $this->container->instance('response', call_user_func($callback));
				} else {
					// unknown callback method
					throw new CannotProcessRouteException('Kernel execution was provided an unprocessable callback');
				}
			}
			
			// no route has triggered an execution, send out a 404
			throw new RouteUnassignedException('Requested path is not assigned to a route');
		}
		
		/**
		 * Assign a route to the router
		 * @param HTTPMethodEnum|array|string|null $method The HTTP method to match against. If null, matches any method
		 * @param string $pattern The regex pattern to match against
		 * @param callable|array|null $callback The callback to run if matched
		 * @return Route
		 */
		protected function assignRoute(
			HTTPMethodEnum|array|string|null $method,
			string $pattern,
			callable|array|null $callback=null
		): Route {
			// @TODO validate $callback
			
			//$this->routeCallbacks[ $pattern ] = $callback;
			
			return $this->routeCollection->add(
				$this->makeRoute($method, $pattern)
			);
		}
		
		/**
		 * Generate a route under the current route collection and return it
		 * @param HTTPMethodEnum|array|string|null $method The HTTP method to match against. If null, matches any method
		 * @param string $pattern The pattern to match against
		 * @return Route The generated route
		 */
		protected function makeRoute(
			HTTPMethodEnum|array|string|null $method=null,
			string $pattern
		): Route {
			// create and add the route to the collection
			// assign route and return it
			return (new Route(
				$this->routeCollection,
				$method,
				$pattern
			))->setRouter($this)->setContainer($this->container);
		}
		
		/**
		 * Attempt to match Request against pattern. If successful override matched parameters in request, set status of router as served
		 * @param string $pattern The pattern to match against
		 * @param string|null $http_method The HTTP method to match against
		 * @return bool
		 */
		protected function attemptPathPattern(string $pattern, string|null $http_method=null): bool {
			if($this->served) {
				return false;
			}
			
			if((null !== $http_method) && ($http_method !== strtoupper($_SERVER['REQUEST_METHOD']))) {
				return false;
			}
			
			if(!preg_match($pattern, $this->container['request']->path(), $raw_matches)) {
				return false;
			}
			
			return true;
		}
		
		/**
		 * Attach a subsequent route collection to add context to
		 * basic Route:: calls inside of group initialization callbacks.
		 * @param RouteCollection $collection
		 * @return void
		 */
		public function attachContext(RouteCollection $collection): void {
			// store the current context
			$this->contextualRouteCollections[] = $this->routeCollection;
			
			// set the new context
			$this->routeCollection = $collection;
		}
		
		/**
		 * Resets the context of the router. Used when a route collection
		 * is finished being defined
		 * @return void
		 */
		public function detachContext(): void {
			// restore the previous context
			$this->routeCollection = array_pop($this->contextualRouteCollections);
		}
		
		/**
		 * Test if the request matches the given path and pattern for any form of request
		 * @param string $pattern The pattern to match against
		 * @param callable|array|null $callback The callback to run if matched
		 * @return bool
		 */
		public function any(string $pattern, callable|array|null $callback=null): Route {
			return $this->assignRoute(null, $pattern, $callback);
		}
		
		/**
		 * Test if the request matches the given path and pattern for GET requests
		 * @param string $pattern The pattern to match against
		 * @param callable|array|null $callback The callback to run if matched
		 * @return bool
		 */
		public function get(string $pattern, callable|array|null $callback=null): Route {
			return $this->assignRoute([
				HTTPMethodEnum::GET,
				HTTPMethodEnum::HEAD
			], $pattern, $callback);
		}
		
		/**
		 * Test if the request matches the given path and pattern for POST requests
		 * @param string $pattern The pattern to match against
		 * @param callable|array|null $callback The callback to run if matched
		 * @return bool
		 */
		public function post(
			string $pattern,
			callable|array|null $callback=null
		): Route {
			return $this->assignRoute(HTTPMethodEnum::POST, $pattern, $callback);
		}
		
		/**
		 * Test if the request matches the given path and pattern for PUT requests
		 * @param string $pattern The pattern to match against
		 * @param callable|array|null $callback The callback to run if matched
		 * @return bool
		 */
		public function put(string $pattern, callable|array|null $callback=null): Route {
			return $this->assignRoute(HTTPMethodEnum::PUT, $pattern, $callback);
		}
		
		/**
		 * Test if the request matches the given path and pattern for PATCH requests
		 * @param string $pattern The pattern to match against
		 * @param callable|array|null $callback The callback to run if matched
		 * @return bool
		 */
		public function patch(string $pattern, callable|array|null $callback=null): Route {
			return $this->assignRoute(HTTPMethodEnum::PATCH, $pattern, $callback);
		}
		
		/**
		 * Test if the request matches the given path and pattern for DELETE requests
		 * @param string $pattern The pattern to match against
		 * @param callable|array|null $callback The callback to run if matched
		 * @return bool
		 */
		public function delete(string $pattern, callable|array|null $callback=null): Route {
			return $this->assignRoute(HTTPMethodEnum::DELETE, $pattern, $callback);
		}
		
		/**
		 * Test if the request matches the given path and pattern for OPTIONS requests
		 * @param string $pattern The pattern to match against
		 * @param callable|array|null $callback The callback to run if matched
		 * @return bool
		 */
		public function options(string $pattern, callable|array|null $callback=null): Route {
			return $this->assignRoute(HTTPMethodEnum::OPTIONS, $pattern, $callback);
		}
		
		/**
		 * Group routes together under a common prefix and pass a child router instance to the callback to run matches against
		 * @param string $pattern The pattern to match against
		 * @param string $method The HTTP method to match against
		 * @return void
		 */
		public function group(string $pathPrefix, callable $callback): void {
			$collection = new RouteCollection(
				$this,
				$this->routeCollection,
				$pathPrefix,
				$callback,
				$this->routeCollection->getNamePrefix()
			);
			
			// attach context to router
			$this->attachContext($collection);
			
			// $router = new Router($this->request, $pathPrefix);
			$callback();
			
			// done with context for router
			$this->detachContext();
			
			// @TMP
			throw new RuntimeException("Router grouping functionality not yet implemented");
		}
	}