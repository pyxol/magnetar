<?php
	declare(strict_types=1);
	
	namespace Magnetar\Kernel\Http;
	
	use Magnetar\Kernel\Kernel as BaseKernel;
	use Magnetar\Application;
	use Magnetar\Http\Request;
	use Magnetar\Http\Response;
	use Magnetar\Router\Router;
	use Magnetar\Template\Template;
	
	use Magnetar\Kernel\KernelPanicException;
	
	class Kernel extends BaseKernel {
		protected Application $app;
		
		public function __construct(
			Application $app
		) {
			$this->app = $app;
			
			// prep request and response objects
			$this->app->instance('request', new Request($this->app));
			$this->app->instance('response', new Response());
			
			// prep router
			$this->app->instance('router', new Router($this->app));
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
		 * Initialize method called by constructor
		 * @return void
		 */
		protected function preprocess(): void {
			$this->bootstrap();
		}
		
		/**
		 * Called after kernel execution
		 * @return void
		 */
		protected function postprocess(): void {
			// currently does nothing
		}
		
		/**
		 * Handle a kernel panic
		 * @return void
		 */
		protected function handlePanic(KernelPanicException $e): void {
			// initialize template engine
			$tpl = new Template();
			
			$this->app['response']->status(503)->send($tpl->render('errors/503', [
				'message' => $e->getMessage(),
			]));
		}
		
		/**
		 * Register a GET route
		 * @param string $pattern The path pattern to attempt to serve. Expects a regex expression. Named regex groups are converted to parameters
		 * @param null|callable|array $callback The callback to execute when the path is requested
		 * @return void
		 * @see Http\Router\Router::attemptPattern()
		 */
		public function get(string $pattern, null|callable|array $callback=null): void {
			// GET request method?
			if($this->app['router']->get($pattern)) {
				// serve request
				//$this->execute($callback, $this->app['request'], $this->app['response']);
				
				// attempt to execute without specifying request/response
				$this->execute($callback);
			}
		}
		
		/**
		 * Register a POST route
		 * @param string $pattern The path pattern to attempt to serve. Expects a regex expression. Named regex groups are converted to parameters
		 * @param null|callable|array $callback The callback to execute when the path is requested
		 * @return void
		 * @see Http\Router\Router::attemptPattern()
		 */
		public function post(string $pattern, null|callable|array $callback): void {
			// POST request method?
			if($this->app['router']->post($pattern)) {
				// serve request
				$this->execute($callback, $this->app['request'], $this->app['response']);
			}
		}
		
		/**
		 * Register a route for any request method
		 * @param string $pattern The path pattern to attempt to serve. Expects a regex expression. Named regex groups are converted to parameters
		 * @param null|callable|array $callback The callback to execute when the path is requested
		 * @return void
		 * @see Http\Router\Router::attemptPattern()
		 */
		public function any(string $pattern, null|callable|array $callback): void {
			// any request method
			if($this->app['router']->any($pattern)) {
				// serve request
				$this->execute($callback, $this->app['request'], $this->app['response']);
			}
		}
		
		/**
		 * Finalize processing HTTP request. If no route is previously matched, this will render a 404
		 * @return void
		 */
		public function serve(): void {
			// no route has triggered an execution, send out a 404
			$this->handle404('Requested path is not found');
		}
		
		/**
		 * Handle a 404 response
		 * @param string $message The message to display
		 * @return void
		 */
		public function handle404(string $message=''): void {
			// initialize template engine
			$tpl = new Template();
			
			$this->app['response']->status(404)->send($tpl->render('errors/404', [
				'message' => $message
			]));
		}
	}