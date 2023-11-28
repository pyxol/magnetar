<?php
	declare(strict_types=1);
	
	namespace Magnetar\Http;
	
	use Throwable;
	use Exception;
	use Closure;
	
	use Magnetar\Application;
	use Magnetar\Http\Request;
	use Magnetar\Http\Response;
	use Magnetar\Router\Router;
	use Magnetar\Helpers\Facades\Facade;
	use Magnetar\Pipeline\Pipeline;
	use Magnetar\Http\ExceptionHandler;
	
	class Kernel {
		/**
		 * Middleware stack to process requests into responses
		 * @var array
		 */
		protected array $middleware = [];
		
		/**
		 * A prioritized list of middleware classes.
		 * The middleware stack will be sorted using this array as a guide. Lookups for
		 * a middleware's interface or parent class is done for matching purposes. This
		 * allows for App-namespaced classes to match with their framework-namespaced counterparts
		 * 
		 * @var array
		 */
		protected array $middlewareSorted = [
			\Magnetar\Http\CoookieJar\Middleware\EncryptCookies::class,
			\Magnetar\Http\CookieJar\Middleware\AddQueuedCookiesToResponse::class,
		];
		
		/**
		 * Array of classes to instantiate and call using kernel->bootstrap()
		 * @var array
		 */
		protected array $bootstrappers = [
			\Magnetar\Bootstrap\LoadConfigs::class,
			\Magnetar\Bootstrap\RegisterFacades::class,
			\Magnetar\Bootstrap\RegisterServiceProviders::class,
			\Magnetar\Bootstrap\BootServiceProviders::class,
		];
		
		public function __construct(
			/**
			 * The application instance
			 * @var Application
			 */
			protected Application $app,
			
			/**
			 * The router instance
			 * @var Router
			 */
			protected Router $router
		) {
			
		}
		
		/**
		 * Bootstrap the application using the kernel's bootstrappers
		 */
		public function bootstrap(): void {
			if(!$this->app->hasBeenBootstrapped()) {
				$this->app->bootstrapWith($this->bootstrappers());
			}
		}
		
		/**
		 * Get the bootstrap classes for the application.
		 * @return array
		 */
		protected function bootstrappers(): array {
			return $this->bootstrappers;
		}
		
		/**
		 * Process a request and generate a response
		 * @param Request $request
		 * @return Response
		 */
		public function process(Request $request): Response {
			$this->app->instance('request', $request);
			Facade::clearResolvedInstance('request');
			
			$this->bootstrap();
			
			$this->sortMiddleware();
			
			try {
				$response = (new Pipeline($this->app))
					->send($request)
					->through($this->middleware)
					->then($this->sendRequestToRouter());
				
				// send to client
				$response->send();
			} catch(Throwable $e) {
				$this->recordException($e);
				
				$response = $this->renderException($request, $e);
			}
			
			return $response;
		}
		
		/**
		 * Record an exception
		 * @param Throwable $e
		 * @return void
		 */
		protected function recordException(Throwable $e): void {
			$this->app[ ExceptionHandler::class ]->record($e);
		}
		
		/**
		 * Render an exception's response
		 * @param Request $request
		 * @param Throwable $e
		 * @return Response
		 */
		protected function renderException(Request $request, Throwable $e): Response {
			return $this->app[ ExceptionHandler::class ]->render($request, $e);
		}
		
		/**
		 * Send a request to the router and return the response
		 * @param Request $request
		 * @return Closure
		 */
		protected function sendRequestToRouter(): Closure {
			//protected function sendRequestToRouter(Request $request): Response {
			//return $this->router->processRequest($request);
			
			return function($request) {
				// request processed through middleware, refresh instance in container
				$this->app->instance('request', $request);
				
				return $this->router->processRequest($request);
			};
		}
		
		/**
		 * Terminate the kernel process
		 * @param Request $request
		 * @param Response $response
		 * @return void
		 */
		public function terminate(Request $request, Response $response): void {
			// push the response through any middleware that have any
			// termination-related functionality
			$this->processTerminationMiddleware($request, $response);
			
			// send response to client
			$response->send();
			
			// terminate app
			$this->app->terminate();
		}
		
		/**
		 * Run through middleware and call any terminate() methods
		 * @param Request $request
		 * @param Response $response
		 * @return void
		 */
		protected function processTerminationMiddleware(Request $request, Response $response): void {
			$middlewares = $this->middleware;
			
			foreach($middlewares as $middleware) {
				if(!is_string($middleware)) {
					continue;
				}
				
				[$name] = $this->parseMiddlewareString($middleware);
				
				$instance = $this->app->make($name);
				
				if(method_exists($instance, 'terminate')) {
					$instance->terminate($request, $response);
				}
			}
		}
		
		/**
		 * Sorts the middleware using the pre-arranged middleware array as a prioritized guide
		 * @return void
		 */
		protected function sortMiddleware(): void {
			$this->middleware = (new MiddlewareSorter(
				$this->middleware,
				$this->middlewareSorted
			))->sorted();
		}
		
		/**
		 * Parse a middleware string into a class name and parameters
		 * @param string $middleware The middleware string to parse
		 * @return array
		 */
		protected function parseMiddlewareString(string $middleware): array {
			$segments = explode(':', $middleware);
			
			$name = $segments[0];
			$params = $segments[1] ?? null;
			
			return [$name, $params];
		}
		
		/**
		 * Handle a kernel panic
		 * @return void
		 */
		protected function panic(Exception $e): Response {
			// send 503 response
			return (new Response())->responseCode(503)->body(
				$this->app->make('theme')->tpl('errors/503', [
					'message' => $e->getMessage(),
					'file' => $e->getFile(),
					'line' => $e->getLine(),
					'trace' => $e->getTraceAsString()
				])
			);
		}
		
		/**
		 * Handle a 404 response
		 * @param string $message The message to display
		 * @return void
		 */
		public function handle404(string $message=''): Response {
			// send 404 response
			return (new Response())->responseCode(404)->body(
				$this->app->make('theme')->tpl('errors/404', [
					'message' => $message
				])
			);
		}
		
		/**
		 * Get the application instance
		 * @return Application The kernel's application instance
		 */
		public function getApplication(): Application {
			return $this->app;
		}
		
		/**
		 * Set the application instance
		 * @param Application $app The application instance
		 * @return self
		 */
		public function setApplication(Application $app): self {
			$this->app = $app;
			
			return $this;
		}
	}