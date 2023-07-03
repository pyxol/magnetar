<?php
	declare(strict_types=1);
	
	namespace Magnetar\Http\Response;
	
	class Response {
		/**
		 * Set the HTTP Response Code
		 * @param int $code The HTTP Response Code. Defaults to 200
		 * @return Response
		 */
		public function status($code=200): Response {
			http_response_code($code);
			
			return $this;
		}
		
		/**
		 * Set a single header
		 * @param string $header The header to set
		 * @return Response
		 */
		public function header(string $header, bool|int $replace=true, int|null $response_code=0): Response {
			header($header, $replace, $response_code);
			
			return $this;
		}
		
		/**
		 * Set a cookie
		 * @param string $name The cookie name
		 * @param string|null $value The cookie value
		 * @param int $expires The cookie expiration time
		 * 		0 = expire at end of session
		 * 		>0 = expire in $expires seconds
		 * 		<0 = expire in abs($expires) seconds
		 * @param string $path The cookie path
		 * @param string $domain The cookie domain
		 * @param bool $secure Whether the cookie should only be sent over HTTPS
		 * @param bool $httponly Whether the cookie should only be accessible over HTTP
		 * @return Response
		 * @note This method is a wrapper for PHP's setcookie() function
		 * @TODO
		 */
		public function setCookie($name, $value=null, $expires=0, $path="", $domain="", $secure=false, $httponly=false): Response {
			setcookie($name, $value, $expires, $path, $domain, $secure, $httponly);
			
			return $this;
		}
		
		/**
		 * Redirect to a specific URL
		 */
		public function redirect(string $path, int $response_code=302): void {
			// sanitize response code
			if(!in_array($response_code, [301, 302, 307])) {
				$response_code = 302;
			}
			
			// sanitize path
			if(!preg_match("#^https?\://#si", $path)) {
				$path = ABS_URI . $path;
			}
			
			// send location header
			$this->header("Location: ". $path, true, $response_code);
		}
		
		/**
		 * Set HTML header and prints HTML response
		 * @param string $body The HTML body to print
		 * @return void
		 */
		public function send($body=""): void {
			$this->header("Content-Type: text/html; charset=UTF-8");
			
			print $body;
		}
		
		/**
		 * Set JSON header and prints JSON response
		 * @param array $body The JSON body to print
		 * @return void
		 */
		public function json(array $body): void {
			$this->header("Content-Type: application/json");
			
			print json_encode($body);
		}
	}