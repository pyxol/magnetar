<?php
	declare(strict_types=1);
	
	namespace Magnetar\Auth\Middleware;
	
	use Closure;
	use RuntimeException;
	
	use Magnetar\Http\Request;
	use Magnetar\Http\Response;
	use Magnetar\Auth\Exceptions\AuthorizationException;
	use Magnetar\Helpers\Facades\Auth;
	
	/**
	 * Middleware to authenticate the request
	 */
	class AuthenticateMiddleware {
		/**
		 * Handle the request after passing authentication
		 * @param Request $request The request instance
		 * @param Closure $next The next middleware
		 * @return Response
		 */
		public function handle(Request $request, Closure $next): Response {
			$this->authenticate($request);
			
			return $next($request);
		}
		
		/**
		 * Authenticate the request
		 * @param Request $request
		 * @return void
		 * 
		 * @throws \Magnetar\Auth\Exceptions\AuthorizationException
		 */
		protected function authenticate(Request $request): void {
			if(app('auth')->attempt($request)) {
				return;
			}
			
			$this->unauthorized($request);
		}
		
		/**
		 * Generate the response for an unauthenticated request
		 * @param Request $request
		 * @return void
		 * 
		 * @throws \Magnetar\Auth\Exceptions\AuthorizationException
		 */
		protected function unauthorized(Request $request): void {
			throw (new AuthorizationException())->respondWith($this->redirect($request));
		}
		
		/**
		 * Generate the response for an unauthenticated request
		 * @param Request $request
		 * @return Response
		 * 
		 * @throws \RuntimeException
		 */
		protected function redirect(Request $request): Response {
			throw new RuntimeException('Please override the AuthenticateMiddleware::redirect method');
		}
	}