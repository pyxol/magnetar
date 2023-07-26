<?php
	declare(strict_types=1);
	
	namespace Magnetar\Http;
	
	use Exception;
	use RuntimeException;
	
	use Magnetar\Application;
	use Magnetar\Http\Request;
	use Magnetar\Http\Response;
	use Magnetar\Router\Router;
	use Magnetar\Helpers\Facades\Facade;
	
	use Magnetar\Http\KernelPanicException;
	use Magnetar\Router\RouteUnassignedException;
	use Magnetar\Router\CannotProcessRouteException;

	class Kernel {
		/**
		 * Middleware stack
		 * @var array
		 */
		protected array $middleware = [];
		
		public function __construct(
			protected Application $app,
			protected Router $router
		) {
			// prep request and response objects
			//$this->app->instance('request', new Request($this->app));
			
			// prep router
			//$this->app->instance('router', new Router($this->app));
		}
		
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
				return $this->router->processRequest($request);
			} catch(CannotProcessRouteException $e) {
				return $this->panic($e);
			} catch(RouteUnassignedException $e) {
				return $this->handle404($e->getMessage());
			} catch(Exception $e) {
				return $this->panic($e);
			}
		}
		
		/**
		 * Terminate the kernel process
		 * @param Request $request
		 * @param Response $response
		 * @return void
		 */
		public function terminate(Request $request, Response $response): void {
			// @TODO
			
			//throw new RuntimeException("Kernel::terminate() not yet implemented");
		}
		
		/**
		 * Handle a kernel panic
		 * @return void
		 */
		protected function panic(Exception $e): Response {
			// send 503 response
			$response = new Response();
			
			$response->status(503)->send(
				$this->app->make('theme')->tpl('errors/503', [
					'message' => $e->getMessage(),
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
			$response = new Response();
			
			$response->status(404)->send(
				$this->app->make('theme')->tpl('errors/404', [
					'message' => $message
				])
			);
			
			return $response;
		}
	}