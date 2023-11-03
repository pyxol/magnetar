<?php
	declare(strict_types=1);
	
	namespace Magnetar\Http;
	
	use Magnetar\Http\HeaderCollection;
	
	class Response {
		/**
		 * The response headers
		 * @var HeaderCollection
		 */
		protected HeaderCollection $headers;
		
		/**
		 * The HTTP Response Code
		 * @var int
		 */
		protected int $response_code = 200;
		
		/**
		 * The response body
		 * @var string
		 */
		protected string $body = '';
		
		/**
		 * Whether the response headers have been sent
		 * @var bool
		 */
		protected bool $sent_headers = false;
		
		/**
		 * Whether the response has been sent (headers and body)
		 * @var bool
		 */
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
		 * @param int $response_code The HTTP Response Code. Defaults to 200
		 * @return self
		 */
		public function responseCode(int $response_code=200): self {
			$this->response_code = $response_code;
			
			return $this;
		}
		
		/**
		 * Set the response body
		 * @param string $body The response body
		 * @return self
		 */
		public function body(string $body=''): self {
			$this->body = $body;
			
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
			http_response_code($this->response_code);
			
			// send headers
			$this->headers->send();
			
			// mark headers as sent
			$this->sent_headers = true;
			
			return $this;
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
		 * Get the response headers
		 * @return array
		 */
		public function headers(): array {
			return $this->headers->all();
		}
		
		/**
		 * Set multiple headers at once. Note, this will replace any existing headers with the same name
		 * @param array $headers An associative array of headers to set. Key is the header name, value is the header value
		 * @return self
		 */
		public function setHeaders(array $headers): self {
			if($this->sent_headers) {
				return $this;
			}
			
			foreach($headers as $header => $value) {
				$this->header($header, $value);
			}
			
			return $this;
		}
		
		/**
		 * Remove a response header by name
		 * @param string $name The header name
		 * @return self
		 */
		public function removeHeader(string $name): self {
			if($this->sent_headers) {
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
			if($this->sent_headers) {
				return $this;
			}
			
			$this->headers->clear();
			
			return $this;
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
		 * Check if the response headers have been sent
		 * @return bool
		 */
		public function sentHeaders(): bool {
			return $this->sent_headers;
		}
		
		/**
		 * Check if the response has been sent
		 * @return bool
		 */
		public function sent(): bool {
			return $this->sent;
		}
		
		/**
		 * Redirect to a specific URL
		 * @param string $path The URL to redirect to
		 * @param int $response_code Optional. HTTP status code. Defaults to 302
		 * @return self
		 */
		public function redirect(string $path, int $response_code=302): self {
			return (new RedirectResponse($path, $response_code))->responseCode($response_code);
		}
		
		/**
		 * Set JSON header and prints JSON response
		 * @param mixed $body The JSON body to print
		 * @return self
		 */
		public function json(mixed $body): self {
			return (new JsonResponse)->json($body);
		}
	}