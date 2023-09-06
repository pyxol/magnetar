<?php
	declare(strict_types=1);
	
	namespace Magnetar\Router;
	
	use RuntimeException;   // @TMP
	
	use Magnetar\Container\Container;
	use Magnetar\Http\Request;
	use Magnetar\Http\Response;
	use Magnetar\Router\Exceptions\RouteUnassignedException;
	use Magnetar\Router\Exceptions\CannotProcessRouteException;
	
	/**
	 * Router class
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
			protected string $prefixPath=''
		) {
			$this->requestMethod = $_SERVER['REQUEST_METHOD'] ?? null;
		}
		
		/**
		 * Group routes together under a common prefix and pass a child router instance to the callback to run matches against
		 * @param string $pattern The pattern to match against
		 * @param string $method The HTTP method to match against
		 * @return void
		 */
		public function group(string $prefixPath, callable $callback): void {
			// $router = new Router($this->request, $prefixPath);
			// $callback($router);
			
			// @TMP
			throw new RuntimeException("Router grouping functionality not yet implemented");
		}
		
		/**
		 * Assign a route to the router
		 * @param string $pattern The regex pattern to match against
		 * @param callable|array|null $callback The callback to run if matched
		 * @param string $method The HTTP method to match against. Defaults to psuedo-method 'ANY'
		 * @return void
		 */
		protected function assignRoute(string $pattern, callable|array|null $callback, string $method='ANY'): void {
			if($this->served) {
				return;
			}
			
			// @TODO likely rearrange routeCallbacks to store pattern+method+callback or convert routeCallbacks into a Routes collection class
			if(($this->requestMethod !== $method) && ('ANY' !== $method)) {
				return;
			}
			
			$this->routeCallbacks[ $pattern ] = $callback;
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
				
				//// @TODO Request shouldn't be responsible for route
				//$this->container['request']->setRoute(
				//	new Route(
				//		$pattern,
				//		$raw_matches,
				//		$this->container['request']
				//	)
				//);
				
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
		 * Test if the request matches the given path and pattern for any form of request
		 * @param string $pattern The pattern to match against
		 * @param callable|array|null $callback The callback to run if matched
		 * @return bool
		 */
		public function any(string $pattern, callable|array|null $callback=null): void {
			$this->assignRoute($pattern, $callback);
		}
		
		/**
		 * Test if the request matches the given path and pattern for GET requests
		 * @param string $pattern The pattern to match against
		 * @param callable|array|null $callback The callback to run if matched
		 * @return bool
		 */
		public function get(string $pattern, callable|array|null $callback=null): void {
			$this->assignRoute($pattern, $callback, 'GET');
		}
		
		/**
		 * Test if the request matches the given path and pattern for POST requests
		 * @param string $pattern The pattern to match against
		 * @param callable|array|null $callback The callback to run if matched
		 * @return bool
		 */
		public function post(string $pattern, callable|array|null $callback=null): void {
			$this->assignRoute($pattern, $callback, 'POST');
		}
		
		/**
		 * Test if the request matches the given path and pattern for PUT requests
		 * @param string $pattern The pattern to match against
		 * @param callable|array|null $callback The callback to run if matched
		 * @return bool
		 */
		public function put(string $pattern, callable|array|null $callback=null): void {
			$this->assignRoute($pattern, $callback, 'PUT');
		}
		
		/**
		 * Test if the request matches the given path and pattern for PATCH requests
		 * @param string $pattern The pattern to match against
		 * @param callable|array|null $callback The callback to run if matched
		 * @return bool
		 */
		public function patch(string $pattern, callable|array|null $callback=null): void {
			$this->assignRoute($pattern, $callback, 'PATCH');
		}
		
		/**
		 * Test if the request matches the given path and pattern for DELETE requests
		 * @param string $pattern The pattern to match against
		 * @param callable|array|null $callback The callback to run if matched
		 * @return bool
		 */
		public function delete(string $pattern, callable|array|null $callback=null): void {
			$this->assignRoute($pattern, $callback, 'DELETE');
		}
		
		/**
		 * Test if the request matches the given path and pattern for HEAD requests
		 * @param string $pattern The pattern to match against
		 * @param callable|array|null $callback The callback to run if matched
		 * @return bool
		 */
		public function head(string $pattern, callable|array|null $callback=null): void {
			$this->assignRoute($pattern, $callback, 'HEAD');
		}
		
		/**
		 * Test if the request matches the given path and pattern for OPTION requests
		 * @param string $pattern The pattern to match against
		 * @param callable|array|null $callback The callback to run if matched
		 * @return bool
		 */
		public function option(string $pattern, callable|array|null $callback=null): void {
			$this->assignRoute($pattern, $callback, 'OPTION');
		}
	}