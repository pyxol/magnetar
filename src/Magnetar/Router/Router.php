<?php
	declare(strict_types=1);
	
	namespace Magnetar\Router;
	
	use Magnetar\Http\Request\Request;
	use Magnetar\Router\Route;
	
	class Router {
		protected Request $request;
		protected bool $served = false;
		protected string $prefixPath;
		
		public function __construct(Request $request, string $prefixPath="") {
			$this->request = $request;
			$this->prefixPath = $prefixPath;
		}
		
		public function group(string $prefixPath, callable $callback) {
			$router = new Router($this->request, $prefixPath);
			
			$callback($router);
		}
		
		/**
		 * Test if the request matches the given path and pattern for GET requests
		 * @param string $pattern The pattern to match against
		 * @return bool
		 */
		public function get($pattern): bool {
			return $this->attemptPathPattern($pattern, 'GET');
		}
		
		/**
		 * Test if the request matches the given path and pattern for POST requests
		 * @param string $pattern The pattern to match against
		 * @return bool
		 */
		public function post(string $pattern): bool {
			return $this->attemptPathPattern($pattern, 'POST');
		}
		
		/**
		 * Test if the request matches the given path and pattern for PUT requests
		 * @param string $pattern The pattern to match against
		 * @return bool
		 */
		public function put(string $pattern): bool {
			return $this->attemptPathPattern($pattern, 'PUT');
		}
		
		/**
		 * Test if the request matches the given path and pattern for PPATCHOST requests
		 * @param string $pattern The pattern to match against
		 * @return bool
		 */
		public function patch(string $pattern): bool {
			return $this->attemptPathPattern($pattern, 'PATCH');
		}
		
		/**
		 * Test if the request matches the given path and pattern for DELETE requests
		 * @param string $pattern The pattern to match against
		 * @return bool
		 */
		public function delete(string $pattern): bool {
			return $this->attemptPathPattern($pattern, 'DELETE');
		}
		
		/**
		 * Test if the request matches the given path and pattern for HEAD requests
		 * @param string $pattern The pattern to match against
		 * @return bool
		 */
		public function head(string $pattern): bool {
			return $this->attemptPathPattern($pattern, 'HEAD');
		}
		
		/**
		 * Test if the request matches the given path and pattern for OPTION requests
		 * @param string $pattern The pattern to match against
		 * @return bool
		 */
		public function option(string $pattern): bool {
			return $this->attemptPathPattern($pattern, 'OPTION');
		}
		
		/**
		 * Test if the request matches the given path and pattern for any form of request
		 * @param string $pattern The pattern to match against
		 * @return bool
		 */
		public function any(string $pattern): bool {
			return $this->attemptPathPattern($pattern);
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
			
			if(!is_null($http_method) && (strtoupper($http_method) !== strtoupper($_SERVER['REQUEST_METHOD']))) {
				return false;
			}
			
			if(!preg_match($pattern, $this->request->getPath(), $raw_matches)) {
				return false;
			}
			
			$this->served = true;
			
			$this->request->setRoute(
				new Route(
					$pattern,
					$raw_matches,
					$this->request
				)
			);
			
			return true;
		}
	}