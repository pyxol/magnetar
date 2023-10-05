<?php
	declare(strict_types=1);
	
	namespace Magnetar\Http;
	
	use Magnetar\Http\HeaderCollection;
	
	class Response {
		protected HeaderCollection $headers;
		protected int $statusCode = 200;
		protected string $body = '';
		protected bool $headersSent = false;
		protected bool $sent = false;
		
		/**
		 * Constructor method
		 */
		public function __construct() {
			$this->headers = new HeaderCollection();
			
			// set default content type
			$this->header('Content-Type', 'text/html; charset=UTF-8');
		}
		
		/**
		 * Set the HTTP Response Code
		 * @param int $code The HTTP Response Code. Defaults to 200
		 * @return self
		 */
		public function status(int $code=200): self {
			$this->statusCode = $code;
			
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
		 * @TODO needs a cookie management class
		 */
		public function setCookie(
			string $name,
			string|null $value=null,
			int $expires=0,
			string $path='',
			string $domain='',
			bool $secure=false,
			bool $httponly=false
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
			$this->header(
				'Location',
				$path,
				true,
				$response_code
			);
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
		 * @param mixed $body The JSON body to print
		 * @return void
		 */
		public function json(mixed $body): self {
			// @TODO turn into a factory method that clones itself and returns
			// a JsonResponse object instead
			
			$this->header('Content-Type', 'application/json');
			
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
			
			// mark headers as sent
			$this->headersSent = true;
			
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
		
		/**
		 * Get the response body
		 * @return string
		 */
		public function body(): string {
			return $this->body;
		}
		
		/**
		 * Get the response status code
		 * @return int
		 */
		public function statusCode(): int {
			return $this->statusCode;
		}
		
		/**
		 * Get or set a single header
		 * @param string $header The header to set
		 * @return string|Response
		 */
		public function header(
			string $header,
			string|null $value=null,
			bool|int $replace=true,
			int|null $response_code=0
		): string|self {
			if(null === $value) {
				// no value provided, get header instead
				return $this->getHeader($header);
			}
			
			// add header
			$this->headers->add(
				$header,
				$value,
				$replace,
				$response_code
			);
			
			return $this;
		}
		
		/**
		 * Set multiple headers at once. Note, this will replace any existing headers with the same name
		 * @param array $headers An associative array of headers to set. Key is the header name, value is the header value
		 * @return self
		 */
		public function setHeaders(array $headers): self {
			if($this->headersSent) {
				return $this;
			}
			
			foreach($headers as $header => $value) {
				$this->header($header, $value);
			}
			
			return $this;
		}
		
		/**
		 * Get the response headers
		 * @return array
		 */
		public function headers(): array {
			return $this->headers->all();
		}
		
		/**
		 * Get a response header by name
		 * @param string $name The header name
		 * @return string|null
		 */
		public function getHeader(string $name): ?string {
			return $this->headers->get($name);
		}
		
		/**
		 * Check if a response header exists
		 * @param string $name The header name
		 * @return bool
		 */
		public function hasHeader(string $name): bool {
			return $this->headers->has($name);
		}
		
		/**
		 * Remove a response header by name
		 * @param string $name The header name
		 * @return self
		 */
		public function removeHeader(string $name): self {
			if($this->headersSent) {
				return $this;
			}
			
			$this->headers->remove($name);
			
			return $this;
		}
		
		/**
		 * Clear all unsent response headers
		 * @return self
		 */
		public function clearHeaders(): self {
			if($this->headersSent) {
				return $this;
			}
			
			$this->headers->clear();
			
			return $this;
		}
		
		/**
		 * Check if the response headers have been sent
		 * @return bool
		 */
		public function headersSent(): bool {
			return $this->headersSent;
		}
		
		/**
		 * Check if the response has been sent
		 * @return bool
		 */
		public function sent(): bool {
			return $this->sent;
		}
	}