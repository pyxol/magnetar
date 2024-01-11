<?php
	declare(strict_types=1);
	
	namespace Magnetar\Http\CookieJar\Middleware;
	
	use Closure;
	
	use Magnetar\Encryption\Encryption;
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
		 * Whether to serialize the cookie values before encrypting
		 * @var bool
		 */
		protected bool $serialize = false;
		
		/**
		 * Encryption instance
		 * @var Encryption
		 */
		protected Encryption $encryption;
		
		/**
		 * Constructor
		 * @param Encryption $encryption Encryption instance
		 */
		public function __construct(Encryption $encryption) {
			$this->encryption = $encryption;
		}
		
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
			// get cookies from request and decrypt them
			// @TODO update to use the (to be created) cookiejar instance from request
			$cookies = $request->cookies();
			
			foreach($cookies as $name => $value) {
				if(in_array($name, $this->exempt)) {
					continue;
				}
				
				try {
					$request->cookie(
						$name,
						$this->encryption->decrypt(
							$value,
							$this->serialize
						)
					);
				} catch(DecryptionException $e) {
					$request->cookie(
						$name,
						null
					);
				}
			}
			
			return $request;
		}
		
		/**
		 * Encrypt the response's cookies
		 * @param Response $response The response instance
		 * @return Response
		 */
		protected function encrypt(Response $response): Response {
			// get cookies from response and encrypt them
			// @TODO update to use the (to be created) cookiejar instance from response->cookies()
			$cookies = $response->cookies();
			
			foreach($cookies as $name => $cookie) {
				if(in_array($name, $this->exempt)) {
					continue;
				}
				
				$response->setCookie(
					$name,
					$cookie->setValue(
						$this->encryption->encrypt(
							$cookie->getValue(),
							$this->serialize
						)
					)
				);
			}
			
			return $response;
		}
	}