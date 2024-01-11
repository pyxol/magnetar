<?php
	declare(strict_types=1);
	
	namespace Magnetar\Router;
	
	use Exception;
	
	use Magnetar\Router\HasAssignableRoutesTrait;
	use Magnetar\Router\Route;
	use Magnetar\Container\Container;
	use Magnetar\Http\Request;
	use Magnetar\Http\Response;
	use Magnetar\Router\RouteCollection;
	use Magnetar\Router\Enums\HTTPMethodEnum;
	use Magnetar\Http\RedirectResponse;
	use Magnetar\Pipeline\Pipeline;
	use Magnetar\Router\Exceptions\RouteUnassignedException;
	use Magnetar\Router\Exceptions\CannotProcessRouteException;
	
	/**
	 * Router class to match requests against routes and generates a response
	 */
	class Router {
		use HasAssignableRoutesTrait;
		
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
		 * Router middleware stack
		 * @var array
		 */
		protected array $middleware = [];
		
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
			
			// assign route to container
			$this->container->instance('route', $route);
			
			// override request parameters with matched route parameters
			$request->assignOverrideParameters($route->parameters());
			
			// fetch the callback for the route from the action registry
			$callback = $this->actionRegistry->get($route->getUniqueID());
			
			// resolve parameter dependencies for the callback. Additionally pass
			// the route's path-defined parameters to the resolver so that they can be
			// injected into the callback by name (eg: /path/{id} => function(string $id) {}})
			$resolved_parameters = (new RouteDependencyResolver($this->container))->resolveParameters(
				$callback,
				$route->parameters()
			);
			
			// pass resolved parameters, execute callback, and return response
			if(is_array($callback) && isset($callback[0]) && is_string($callback[0])) {
				$callback[0] = new ($callback[0])($this);
			}
			
			// ensure the callback is callable
			if(!is_callable($callback)) {
				// unknown callback method
				throw new CannotProcessRouteException('Route matched has an unprocessable callback');
			}
			
			// send the request through the route middleware stack, generating and returning the callback response instance
			return $this->container->instance('response',
				(new Pipeline($this->container))
					->send($this->container->instance('request', $request))
					->through($this->gatherMiddleware())
					->then(fn ($request) => call_user_func($callback, ...$resolved_parameters))
			);
		}
		
		/**
		 * Gather middleware from the route collection and router
		 * @return array
		 * 
		 *  @TODO
		 */
		protected function gatherMiddleware(): array {
			//// gather middleware from router
			//$middleware = $this->middleware;
			//
			//// gather middleware from route
			//$middleware = array_merge($middleware, route()->getMiddleware());
			//
			//// return the gathered middleware
			//return $middleware;
			
			// gather router middleware and route middleware, removing duplicates
			return array_unique(array_merge($this->middleware, route()->getMiddleware()));
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
			HTTPMethodEnum|array|string|null $method,
			string $pattern
		): Route {
			// create a route using the route collection
			return $this->routeCollection->makeRoute($method, $pattern);
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
		 * Group routes together under a common prefix and pass a child router instance to the callback to run matches against. Returns the contextualized route collection
		 * @param string $pattern The pattern to match against
		 * @param string $method The HTTP method to match against
		 * @return RouteCollection
		 */
		public function group(string $pathPrefix, callable $callback): RouteCollection {
			// instantiate a route collection, pass in the context, path prefix, and callback that will
			// use a temporary router context to define routes. Automatically reverts context when finished
			return (new RouteCollection(
				$this,
				$this->routeCollection
			))->group($pathPrefix, $callback);
		}
		
		/**
		 * Register middleware 
		 * @param string|array $middleware
		 * @return RouteCollection
		 */
		public function middleware(string|array $middleware): RouteCollection {
			// start a route collection and set it's middleware
			return (new RouteCollection(
				$this,
				$this->routeCollection
			))->middleware($middleware);
		}
		
		/**
		 * Export the router's route collection to a serialized string
		 * @return array
		 */
		public function export(): array {
			// export the router's route collection to a serialized string
			return $this->routeCollection->export();
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
				default => throw new Exception('Router method ['. $name .'] does not exist')
			};
		}
	}