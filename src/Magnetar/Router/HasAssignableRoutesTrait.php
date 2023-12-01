<?php
	declare(strict_types=1);
	
	namespace Magnetar\Router;
	
	use Magnetar\Router\Route;
	use Magnetar\Router\Enums\HTTPMethodEnum;
	
	trait HasAssignableRoutesTrait {
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
			return $this->assignRoute(
				null,
				$pattern,
				$callback
			);
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
			return $this->assignRoute(
				HTTPMethodEnum::POST,
				$pattern,
				$callback
			);
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
			return $this->assignRoute(
				HTTPMethodEnum::PUT,
				$pattern,
				$callback
			);
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
			return $this->assignRoute(
				HTTPMethodEnum::PATCH,
				$pattern,
				$callback
			);
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
			return $this->assignRoute(
				HTTPMethodEnum::DELETE,
				$pattern,
				$callback
			);
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
			return $this->assignRoute(
				HTTPMethodEnum::OPTIONS,
				$pattern,
				$callback
			);
		}
		
		/**
		 * Assign a route to the router that matches the given HTTP method(s)
		 * @param HTTPMethodEnum|array|string $methods The HTTP method(s) to match against. String(s) or enum(s) accepted
		 * @param string $pattern The pattern to match against
		 * @param callable|array|string|null|null $callback The callback to run if matched
		 * @return Route
		 * 
		 * @see \Magnetar\Router\Enums\HTTPMethodEnum for valid HTTP method names
		 */
		public function match(
			HTTPMethodEnum|array|string $methods,
			string $pattern,
			callable|array|string|null $callback=null
		): Route {
			return $this->assignRoute(
				$methods,
				$pattern,
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
	}