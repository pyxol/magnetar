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
		 * Middleware stack to process requests through
		 * @var array
		 */
		protected array $middleware = [];
		
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
			
			try {
				$response = (new Pipeline($this->app))
					->send($request)
					->through($this->middleware)
					->then($this->sendRequestToRouter());
				
				// send to client
				// @TODO currently doesnt work because of the way the router is set up
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
			$this->app[ExceptionHandler::class]->record($e);
		}
		
		/**
		 * Render an exception's response
		 * @param Request $request
		 * @param Throwable $e
		 * @return Response
		 */
		protected function renderException(Request $request, Throwable $e): Response {
			return $this->app[ExceptionHandler::class]->render($request, $e);
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
			$response = (new Response())->status(503)->setBody(
				$this->app->make('theme')->tpl('errors/503', [
					'message' => $e->getMessage(),
					'file' => $e->getFile(),
					'line' => $e->getLine(),
					'trace' => $e->getTraceAsString()
				])
			);
			
			return $response;
		}
		
		/**
		 * Handle a 404 response
		 * @param string $message The message to display
		 * @return void
		 */
		public function handle404(string $message=''): Response {
			// send 404 response
			$response = (new Response())->status(404)->setBody(
				$this->app->make('theme')->tpl('errors/404', [
					'message' => $message
				])
			);
			
			return $response;
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