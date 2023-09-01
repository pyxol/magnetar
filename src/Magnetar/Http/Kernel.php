<?php
	declare(strict_types=1);
	
	namespace Magnetar\Http;
	
	use Exception;
	
	use Magnetar\Application;
	use Magnetar\Http\Request;
	use Magnetar\Http\Response;
	use Magnetar\Router\Router;
	use Magnetar\Helpers\Facades\Facade;
	use Magnetar\Router\RouteUnassignedException;
	use Magnetar\Router\Exceptions\CannotProcessRouteException;

	class Kernel {
		/**
		 * Middleware stack
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
			protected Application $app,
			protected Router $router
		) {
			// @TODO router middleware
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
		protected function bootstrappers() {
			return $this->bootstrappers;
		}
		
		/**
		 * Process a request and generate a response
		 * @param Request $request
		 * @return Response
		 */
		public function process(Request $request): Response {
			// @TODO process middleware against request instance
			
			$this->app->instance('request', $request);
			Facade::clearResolvedInstance('request');
			
			$this->bootstrap();
			
			try {
				return $this->sendRequestToRouter($request);
			} catch(CannotProcessRouteException $e) {
				return $this->panic($e);
			} catch(RouteUnassignedException $e) {
				return $this->handle404($e->getMessage());
			} catch(Exception $e) {
				return $this->panic($e);
			}
		}
		
		/**
		 * Send a request to the router and return the response
		 * @param Request $request
		 * @return Response
		 */
		protected function sendRequestToRouter(Request $request): Response {
			return $this->router->processRequest($request);
		}
		
		/**
		 * Terminate the kernel process
		 * @param Request $request
		 * @param Response $response
		 * @return void
		 */
		public function terminate(Request $request, Response $response): void {
			// @TODO process middleware against response instance
			
			$response->send();
			
			// terminate app
			$this->app->terminate();
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