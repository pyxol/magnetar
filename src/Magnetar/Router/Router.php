<?php
	declare(strict_types=1);
	
	namespace Magnetar\Router;
	
	use Exception;
	
	use Magnetar\Container\Container;
	use Magnetar\Http\Request;
	use Magnetar\Http\Response;
	use Magnetar\Router\RouteCollection;
	use Magnetar\Router\Enums\HTTPMethodEnum;
	use Magnetar\Http\RedirectResponse;
	use Magnetar\Router\Exceptions\RouteUnassignedException;
	use Magnetar\Router\Exceptions\CannotProcessRouteException;
	
	/**
	 * Router class to match requests against routes and generates a response
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
		 * The route action registry
		 * @var RouteActionRegistry
		 */
		protected RouteActionRegistry $actionRegistry;
		
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
			
			// create the route action registry
			$this->actionRegistry = new RouteActionRegistry();
		}
		
		/**
		 * Turn the request into a response by matching against the registered routes
		 * @param Request $request The request
		 * @return Response The response
		 * 
		 * @throws CannotProcessRouteException
		 * @throws RouteUnassignedException
		 * @throws UnresolvableRouteParameterException
		 * 
		 * @note For web requests, called by Http\Kernel::sendRequestToRouter() via Http\Kernel::process(Request $request)
		 */
		public function processRequest(Request $request): Response {
			// attempt to match the request against the registered routes
			if(null === ($route = $this->routeCollection->matchRequestToRoute(
				$request->method(),
				$request->path()
			))) {
				// no route matches request, send out a 404
				throw new RouteUnassignedException('Requested path is not assigned to a route');
			}
			
			// override request parameters with matched route parameters
			$request->assignOverrideParameters($route->parameters());
			
			// fetch the callback for the route from the action registry
			$callback = $this->actionRegistry->get($route->getName());
			
			// resolve parameter dependencies for the callback. Additionally pass
			// the route's path-defined parameters to the resolver so that they can be
			// injected into the callback by name (eg: /path/{id} => function(string $id) {}})
			$resolved_parameters = (new RouteDependencyResolver($this->container))->resolveParameters(
				$callback,
				$route->parameters()
			);
			
			// pass resolved parameters, execute callback, and return response
			if(is_array($callback)) {
				// class reference and method
				list($instance, $method) = $callback;
				
				if(is_string($instance)) {
					$instance = new ($instance)($this);
				}
				
				return $this->container->instance('response', call_user_func([$instance, $method], ...$resolved_parameters));
			} elseif(is_callable($callback)) {
				// callable/closure
				return $this->container->instance('response', call_user_func($callback, ...$resolved_parameters));
			} else {
				// unknown callback method
				throw new CannotProcessRouteException('Route matched has an unprocessable callback');
			}
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
			callable|array|string|null $callback=null
		): Route {
			return $this->actionRegistry->register(
				$this->routeCollection->add(
					$this->makeRoute($method, $pattern)
				),
				$callback
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
			//return (new Route(
			//	$this->routeCollection,
			//	$method,
			//	$pattern
			//))->setRouter($this)->setContainer($this->container);
			return $this->routeCollection->makeRoute(
				$method,
				$pattern
			);
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
		 * Create router context by attaching a new, temporary route collection to
		 * a route collection stack so that routes can be defined inside of a group
		 * callback.
		 * As an example, this allows Route::get() calls inside of Route::group(..., fn() => { ... })
		 * to be defined inside of the context of the group's context (name, path prefix, etc.)
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
		 * Determine if trailing slashes are optional when matching against a route
		 * @return bool
		 */
		public function isTrailingSlashOptional(): bool {
			return true;
		}
		
		/**
		 * Assign a route to the router that matches any HTTP method
		 * @param string $pattern The pattern to match against
		 * @param callable|array|string|null $callback The callback to run if matched
		 * @return bool
		 */
		public function any(
			string $pattern,
			callable|array|string|null $callback=null
		): Route {
			return $this->assignRoute(null, $pattern, $callback);
		}
		
		/**
		 * Assign a route to the router that matches GET and HEAD HTTP methods
		 * @param string $pattern The pattern to match against
		 * @param callable|array|string|null $callback The callback to run if matched
		 * @return bool
		 */
		public function get(
			string $pattern,
			callable|array|string|null $callback=null
		): Route {
			return $this->assignRoute([
				HTTPMethodEnum::GET,
				HTTPMethodEnum::HEAD
			], $pattern, $callback);
		}
		
		/**
		 * Assign a route to the router that matches POST HTTP methods
		 * @param string $pattern The pattern to match against
		 * @param callable|array|string|null $callback The callback to run if matched
		 * @return bool
		 */
		public function post(
			string $pattern,
			callable|array|string|null $callback=null
		): Route {
			return $this->assignRoute(HTTPMethodEnum::POST, $pattern, $callback);
		}
		
		/**
		 * Assign a route to the router that matches PUT HTTP methods
		 * @param string $pattern The pattern to match against
		 * @param callable|array|string|null $callback The callback to run if matched
		 * @return bool
		 */
		public function put(
			string $pattern,
			callable|array|string|null $callback=null
		): Route {
			return $this->assignRoute(HTTPMethodEnum::PUT, $pattern, $callback);
		}
		
		/**
		 * Assign a route to the router that matches PATCH HTTP methods
		 * @param string $pattern The pattern to match against
		 * @param callable|array|string|null $callback The callback to run if matched
		 * @return bool
		 */
		public function patch(
			string $pattern,
			callable|array|string|null $callback=null
		): Route {
			return $this->assignRoute(HTTPMethodEnum::PATCH, $pattern, $callback);
		}
		
		/**
		 * Assign a route to the router that matches DELETE HTTP methods
		 * @param string $pattern The pattern to match against
		 * @param callable|array|string|null $callback The callback to run if matched
		 * @return bool
		 */
		public function delete(
			string $pattern,
			callable|array|string|null $callback=null
		): Route {
			return $this->assignRoute(HTTPMethodEnum::DELETE, $pattern, $callback);
		}
		
		/**
		 * Assign a route to the router that matches OPTIONS HTTP methods
		 * @param string $pattern The pattern to match against
		 * @param callable|array|string|null $callback The callback to run if matched
		 * @return bool
		 */
		public function options(
			string $pattern,
			callable|array|string|null $callback=null
		): Route {
			return $this->assignRoute(HTTPMethodEnum::OPTIONS, $pattern, $callback);
		}
		
		/**
		 * Group routes together under a common prefix and pass a child router instance to the callback to run matches against. Returns the contextualized route collection
		 * @param string $pattern The pattern to match against
		 * @param string $method The HTTP method to match against
		 * @return RouteCollection
		 */
		public function group(string $pathPrefix, callable $callback): RouteCollection {
			// instantiate a route collection, pass in the context, path prefix, and callback that will
			// use a temporary router context to define routes. Automatically reverts context when finished
			return new RouteCollection(
				$this,
				$this->routeCollection,
				$pathPrefix,
				$callback
			);
		}
		
		/**
		 * Assign a redirect rule for a given path
		 * @param string $pattern The pattern to match against
		 * @param string $redirect_path The URI to redirect to
		 * @param int $response_code The HTTP response code to use. Defaults to 302
		 * @return Route
		 */
		public function redirect(string $pattern, string $redirect_path, int $response_code=302): Route {
			return $this->assignRoute(
				null,
				$pattern,
				fn() => $this->container->instance('response', (new RedirectResponse)->responseCode($response_code)->to($redirect_path))
			);
		}
		
		/**
		 * Assign a permanent redirect (301) rule for a given path
		 * @param string $pattern The pattern to match against
		 * @param string $redirect_path The URI to redirect to
		 * @return Route
		 */
		public function permanentRedirect(string $pattern, string $redirect_path): Route {
			return $this->assignRoute(
				null,
				$pattern,
				fn() => $this->container->instance('response', (new RedirectResponse)->permanent()->to($redirect_path))
			);
		}
		
		/**
		 * Catch magic method calls and assign routes based on the method name if it matches a valid HTTP method name
		 * @param string $name The method name
		 * @param array $arguments The arguments passed to the method
		 * @return mixed
		 */
		public function __call(string $name, array $arguments): mixed {
			return match($name) {
				//'any' => $this->assignRoute(null, ...$arguments),
				//'get' => $this->assignRoute([
				//	HTTPMethodEnum::GET,
				//	HTTPMethodEnum::HEAD
				//], ...$arguments),
				//'post' => $this->assignRoute(HTTPMethodEnum::POST, ...$arguments),
				//'put' => $this->assignRoute(HTTPMethodEnum::PUT, ...$arguments),
				//'patch' => $this->assignRoute(HTTPMethodEnum::PATCH, ...$arguments),
				//'delete' => $this->assignRoute(HTTPMethodEnum::DELETE, ...$arguments),
				//'options' => $this->assignRoute(HTTPMethodEnum::OPTIONS, ...$arguments),
				default => throw new Exception("Router method ". $name ." does not exist")
			};
		}
	}