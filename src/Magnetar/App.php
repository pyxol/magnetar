<?php
	declare(strict_types=1);
	
	namespace Magnetar;
	
	use Magnetar\Http\Request\Request;
	use Magnetar\Http\Response\Response;
	use Magnetar\Router\Router;
	use Magnetar\Router\Route;
	use Magnetar\Template\Template;
	
	class App {
		protected Request $request;
		protected Response $response;
		protected Router $router;
		protected Route $route;
		
		protected bool $served = false;
		
		/**
		 * App constructor.
		 * @return App
		 */
		public function __construct() {
			// prepare the request and response objects
			$this->request = new Request();
			$this->response = new Response();
			
			$this->router = new Router($this->request);
			
			// @TODO switch this into a template theme system
			if(is_on_admin_page()) {
				require_once(INCLUDE_DIR ."admin.php");
				
				// default template directory name
				define('TEMPLATE_DIR_NAME', DEFAULT_ADMIN_TEMPLATE);
				
				// generate templates directory path
				define('TEMPLATES', TEMPLATE_DIR . TEMPLATE_DIR_NAME ."/");
			} else {
				// default template directory name
				define('TEMPLATE_DIR_NAME', DEFAULT_PUBLIC_TEMPLATE);
				
				// generate templates directory path
				define('TEMPLATES', TEMPLATE_DIR . TEMPLATE_DIR_NAME ."/");
			}
			
			//require_once(TEMPLATES ."functions.php");
		}
		
		/**
		 * Register a GET route
		 * @param string $pattern The path pattern to attempt to serve. Expects a regex expression. Named regex groups are converted to parameters
		 * @param null|callable|array $callback The callback to execute when the path is requested
		 * @return void
		 * @see App\Router\Router::attemptPattern()
		 */
		public function get(string $pattern, null|callable|array $callback=null): void {
			// already processed?
			if(!$this->served && $this->router->get($pattern)) {
				// serve request
				$this->serve($callback);
			}
		}
		
		/**
		 * Register a POST route
		 * @param string $pattern The path pattern to attempt to serve. Expects a regex expression. Named regex groups are converted to parameters
		 * @param null|callable|array $callback The callback to execute when the path is requested
		 * @return void
		 * @see App\Router\Router::attemptPattern()
		 */
		public function post(string $pattern, null|callable|array $callback): void {
			// match path?
			if(!$this->served && $this->router->post($pattern)) {
				// serve request
				$this->serve($callback);
			}
		}
		
		/**
		 * Register a route for any request method
		 * @param string $pattern The path pattern to attempt to serve. Expects a regex expression. Named regex groups are converted to parameters
		 * @param null|callable|array $callback The callback to execute when the path is requested
		 * @return void
		 * @see App\Router\Router::attemptPattern()
		 */
		public function any(string $pattern, null|callable|array $callback): void {
			// already processed?
			if(!$this->served && $this->router->any($pattern)) {
				// serve request
				$this->serve($callback);
			}
		}
		
		/**
		 * Process the route and serve response
		 * @param callable|array $callback The callback to execute when the path is requested
		 */
		public function serve(callable|array $callback=null) {
			if($this->served) {
				return;
			}
			
			try {
				$this->served = true;
				
				// determine the page
				if(is_null($callback)) {
					throw new \Exception("Page not found");
				}
				
				$this->handle_callback($callback);
			} catch(\Exception $e) {
				$this->handlePanic($e->getMessage());
			}
		}
		
		/**
		 * Handle the callback
		 * @param callable|array $callback The callback to execute
		 */
		protected function handle_callback(array|callable $callback): void {
			if(is_array($callback)) {
				// class reference and method
				list($class, $method) = $callback;
				
				$instance = new ($class)($this);
				$instance->$method($this->request, $this->response);
			} elseif(is_callable($callback)) {
				// closure
				call_user_func_array($callback, [$this->request, $this->response]);
			} else {
				// unknown callback method
				$this->handlePanic("Unable to process callback");
			}
		}
		
		/**
		 * Handle a 404 response
		 * @param string $message The message to display
		 * @return void
		 */
		public function handle404(string $message=""): void {
			// initialize template engine
			$tpl = new Template();
			
			$this->response->status(404)->send($tpl->render('errors/404', [
				'message' => $message
			]));
		}
		
		/**
		 * Handle a 404 response
		 * @return void
		 */
		public function handlePanic(string|null $message=null): void {
			// initialize template engine
			$tpl = new Template();
			
			$this->response->status(503)->send($tpl->render('errors/503', [
				'message' => $message
			]));
		}
	}