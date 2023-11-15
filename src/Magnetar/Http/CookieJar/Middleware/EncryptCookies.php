<?php
	declare(strict_types=1);
	
	namespace Magnetar\Http\CookieJar\Middleware;
	
	use Closure;
	
	use Magnetar\Http\CookieJar\CookieJar;
	use Magnetar\Http\Request;
	use Magnetar\Http\Response;
	
	/**
	 * Middleware that automatically decrypts cookies from the request and encrypts the response's outbound cookies
	 */
	class EncryptCookies {
		/**
		 * Cookie names that are exempt from automatic encryption/decryption
		 * @var array
		 */
		protected array $exempt = [];
		
		/**
		 * Handle the request
		 * @param Request $request
		 */
		public function handle(Request $request, Closure $next): Response {
			return $this->encrypt(
				$next(
					$this->decrypt($request)
				)
			);
		}
		
		/**
		 * Decrypt the request's cookies
		 * @param Request $request The request instance
		 * @return Request
		 */
		protected function decrypt(Request $request): Request {
			// @TODO get cookies from request and decrypt them
			
			return $request;
		}
		
		/**
		 * Encrypt the response's cookies
		 * @param Response $response The response instance
		 * @return Response
		 */
		protected function encrypt(Response $response): Response {
			// @TODO get cookies from response and encrypt them
			
			return $response;
		}
	}