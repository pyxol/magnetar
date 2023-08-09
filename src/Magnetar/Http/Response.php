<?php
	declare(strict_types=1);
	
	namespace Magnetar\Http;
	
	use Magnetar\Http\HeaderCollection;
	
	class Response {
		protected HeaderCollection $headers;
		protected int $statusCode = 200;
		protected string $body = '';
		protected bool $sent = false;
		
		/**
		 * Constructor method
		 */
		public function __construct() {
			$this->headers = new HeaderCollection();
		}
		
		/**
		 * Set the HTTP Response Code
		 * @param int $code The HTTP Response Code. Defaults to 200
		 * @return self
		 */
		public function status($code=200): self {
			$this->statusCode = $code;
			
			return $this;
		}
		
		/**
		 * Set a single header
		 * @param string $header The header to set
		 * @return Response
		 */
		public function header(string $header, bool|int $replace=true, int|null $response_code=0): self {
			$this->headers->add($header, $replace, $response_code);
			
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
		 * 
		 * @TODO
		 */
		public function setCookie(
			$name,
			$value=null,
			$expires=0,
			$path="",
			$domain="",
			$secure=false,
			$httponly=false
		): self {
			setcookie($name, $value, $expires, $path, $domain, $secure, $httponly);
			
			return $this;
		}
		
		/**
		 * Redirect to a specific URL
		 */
		public function redirect(string $path, int $response_code=302): void {
			// sanitize response code
			if(!in_array($response_code, [300, 301, 302, 303, 304, 307, 308])) {
				$response_code = 302;
			}
			
			// sanitize path
			if(!preg_match("#^https?\://#si", $path)) {
				//$path = ABS_URI . $path;
				$path = config('app.url') . $path;
			}
			
			// send location header
			$this->header("Location: ". $path, true, $response_code);
		}
		
		/**
		 * Set the response body
		 * @param string $body
		 * @return self
		 */
		public function setBody(string $body=''): self {
			$this->body = $body;
			
			return $this;
		}
		
		/**
		 * Set JSON header and prints JSON response
		 * @param array $body The JSON body to print
		 * @return void
		 */
		public function json(array $body): self {
			// @TODO turn into a factory method
			
			$this->header("Content-Type: application/json", true);
			
			$this->setBody(json_encode($body));
			
			return $this;
		}
		
		/**
		 * Send the response to the client
		 * @return self
		 */
		public function send(): self {
			if($this->sent) {
				return $this;
			}
			
			$this->header("Content-Type: text/html; charset=UTF-8", true);
			
			$this->sendHeaders();
			$this->sendBody();
			
			return $this;
		}
		
		/**
		 * Send all headers
		 * @return self
		 */
		public function sendHeaders(): self {
			if(headers_sent()) {
				// too late to send headers now
				return $this;
			}
			
			// send status code
			http_response_code($this->statusCode);
			
			// send headers
			$this->headers->send();
			
			return $this;
		}
		
		/**
		 * Print the response body to the output buffer
		 * @return self
		 */
		public function sendBody(): self {
			print $this->body;
			
			$this->sent = true;
			
			return $this;
		}
	}