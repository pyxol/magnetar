<?php
	declare(strict_types=1);
	
	namespace Magnetar\Http;
	
	use Magnetar\Router\Enums\HTTPMethodEnum;
	use Magnetar\Router\Helpers\HTTPMethodEnumResolver;
	use Magnetar\Http\UploadedFile;
	
	/**
	 * Represents an HTTP request from the client
	 */
	class Request {
		/**
		 * The requested path (without query string)
		 * @var string
		 */
		protected string $path = '';
		
		/**
		 * The pattern that was matched
		 * @var string|null
		 */
		protected ?string $matched_pattern = null;
		
		/**
		 * Request parameters
		 * @var array
		 */
		protected array $parameters = [];
		
		/**
		 * The request method
		 * @var string|null
		 */
		protected ?HTTPMethodEnum $method = null;
		
		/**
		 * Request headers
		 * @var HeaderCollection|null
		 */
		protected ?HeaderCollection $headers = null;
		
		/**
		 * Request cookies
		 * @var array Assoc array of cookies
		 */
		protected array $cookies = [];
		
		/**
		 * Create a new Request object
		 * @param string $path The requested path (without query string)
		 */
		public function __construct() {
			// parse the request's http method
			$this->processRequestMethod();
			
			// parse the request path and query string
			$this->processRequestPath();
			
			// parse the request's cookies
			$this->processRequestCookies();
			
			// parse the request's headers
			$this->processHeaders();
		}
		
		/**
		 * Process the request method
		 * @return void
		 */
		protected function processRequestMethod(): void {
			$this->method = HTTPMethodEnumResolver::resolve(
				$_SERVER['REQUEST_METHOD'] ?? null,
				HTTPMethodEnum::GET
			);
		}
		
		/**
		 * Process the request path
		 * @return void
		 */
		protected function processRequestPath(): void {
			$path = $_SERVER['REQUEST_URI'];
			
			// sanitize request path
			$path = rtrim($path, '?&');
			
			if(false !== ($q_pos = strpos($path, '?'))) {
				// request has ?, save to request and parse parameters
				parse_str(
					substr($path, ($q_pos + 1)),
					$this->parameters
				);
				
				// chop off query string from request path
				$path = substr($path, 0, $q_pos);
			}
			
			// trim leading and trailing slashes
			$path = trim($path, '/');
			
			$this->path = $path;
		}
		
		/**
		 * Process the cookies sent along with the request
		 * @return void
		 */
		public function processRequestCookies(): void {
			$cookies = [];
			
			foreach($_COOKIE as $name => $value) {
				$cookies[ $name ] = $value;
			}
			
			$this->cookies = $cookies;
		}
		
		/**
		 * Create a new Request object
		 * @return Request
		 */
		public static function create(): Request {
			return new static();
		}
		
		/**
		 * Get the raw request headers as an associative array.
		 * Uses filtered values from the $_SERVER global
		 * @return array
		 */
		protected function rawHeaders(): array {
			$headers = [];
			
			foreach($_SERVER as $key => $value) {
				if(str_starts_with($key, 'HTTP_')) {
					$headers[ strtr(substr($key, 5), '_', '-') ] = $value;
				} elseif(in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH', 'CONTENT_MD5'])) {
					$headers[ strtr($key, '_', '-') ] = $value;
				}
			}
			
			return $headers;
		}
		
		/**
		 * Record request headers
		 * @return void
		 */
		protected function processHeaders(): void {
			$this->headers = new HeaderCollection(
				$this->rawHeaders()
			);
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
		public function header(string $name): ?string {
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
		 * Get the requested path
		 * @return string
		 */
		public function path(): string {
			return $this->path;
		}
		
		/**
		 * Get the request method
		 * @return ?HTTPMethodEnum
		 */
		public function method(): ?HTTPMethodEnum {
			return $this->method;
		}
		
		/**
		 * Pass in parameters from the route that override parameters found in URI query params (eg those found in ?...).
		 * Called by Router::processRequest()
		 * @param array $parameters Assoc array of arameters to override
		 * @return void
		 */
		public function assignOverrideParameters(array $parameters): void {
			foreach($parameters as $name => $value) {
				if('' === ($name = strtolower(trim($name)))) {
					continue;
				}
				
				$this->parameters[ $name ] = $value;
			}
		}
		
		/**
		 * Get a parameter from the request
		 * @param string $name The name of the parameter to get
		 * @param mixed $default Optional. Return this if requested parameter isn't set
		 * @return mixed
		 */
		public function parameter(string $name, mixed $default=null): mixed {
			//if('' === ($name = strtolower(trim($name)))) {
			//	return $default;
			//}
			
			return $this->parameters[ $name ] ?? $default;
		}
		
		/**
		 * Alias of Request::parameter()
		 * @param string $name The name of the parameter to get
		 * @param mixed $default Optional. Return this if requested parameter isn't set
		 * @return mixed The parameter value
		 */
		public function get(string $name, mixed $default=null): mixed {
			return $this->parameter($name, $default);
		}
		
		/**
		 * Determine if a parameter exists
		 * @param string $name The name of the parameter to check
		 * @return bool True if the parameter exists, false otherwise
		 */
		public function isset(string $name): bool {
			return isset($this->parameters[ $name ]);
		}
		
		/**
		 * Get all parameters from the request
		 * @return array
		 */
		public function parameters(): array {
			return $this->parameters;
		}
		
		/**
		 * Set the value of a specific parameter
		 * @param string $name The name of the parameter to overwrite
		 * @param mixed $value The new value of the parameter
		 * @return void
		 */
		public function setParameter(string $name, mixed $value): void {
			$this->parameters[ $name ] = $value;
		}
		
		/**
		 * Remove a parameter from the request
		 * @param string $name The name of the parameter to remove
		 * @return void
		 */
		public function removeParameter(string $name): void {
			unset($this->parameters[ $name ]);
		}
		
		/**
		 * Get the request's cookies as an associative array
		 * @return array
		 */
		public function cookies(): array {
			return $this->cookies;
		}
		
		/**
		 * Get the raw request body
		 * @return string
		 */
		public function body(): string {
			return file_get_contents('php://input') ?: '';
		}
		
		/**
		 * Get an instance of UploadedFile for the specified input name. If the input name that was sent is an array, an array of UploadedFile instances will be returned. If the input name is not found, null is returned.
		 * @param string $input_name The name of the input to get the uploaded file for
		 * @return UploadedFile|array|null
		 */
		public function file(string $input_name): UploadedFile|array|null {
			if(!isset($_FILES[ $input_name ])) {
				return null;
			}
			
			$file = $_FILES[ $input_name ];
			
			if(is_array($file['tmp_name'])) {
				$files = [];
				
				foreach($file['tmp_name'] as $index => $tmp_name) {
					$files[] = new UploadedFile(
						$tmp_name,
						$file['name'][ $index ],
						$file['type'][ $index ],
						$file['size'][ $index ],
						$file['error'][ $index ]
					);
				}
				
				return $files;
			}
			
			return new UploadedFile(
				$file['tmp_name'],
				$file['name'],
				$file['type'],
				$file['size'],
				$file['error']
			);
		}
		
		/**
		 * Get all uploaded files
		 * @return array
		 */
		public function files(): array {
			$files = [];
			
			foreach($_FILES as $input_name => $file) {
				if(is_array($file['tmp_name'])) {
					$files[ $input_name ] = [];
					
					foreach(array_keys($file['tmp_name']) as $key) {
						$files[ $input_name ][ $key ] = new UploadedFile(
							$file['tmp_name'][ $key ],
							$file['name'][ $key ],
							$file['type'][ $key ],
							$file['size'][ $key ],
							$file['error'][ $key ]
						);
					}
				} else {
					$files[ $input_name ] = new UploadedFile(
						$file['tmp_name'],
						$file['name'],
						$file['type'],
						$file['size'],
						$file['error']
					);
				}
			}
			
			return $files;
		}
		
		/**
		 * Determine if the request accepts the specified content type
		 * @param array|string $content_types The content type(s) to check
		 * @return bool
		 */
		public function accepts(array|string $content_type): bool {
			if(null === ($header = $this->header('Accept'))) {
				return true;
			}
			
			if(!is_array($content_type)) {
				$content_type = [$content_type];
			}
			
			$accepts = explode(',', strtolower($header));
			
			foreach($accepts as $accept) {
				if(('*' === $accept) || ('*/*' === $accept)) {
					return true;
				}
				
				foreach($content_type as $content_type) {
					$content_type = strtolower($content_type);
					
					if($content_type === $accept) {
						return true;
					}
					
					if($accept === strtok($content_type, '/') .'/*') {
						return true;
					}
				}
			}
			
			return false;
		}
		
		/**
		 * Determine if the request expects any content type
		 * @return bool
		 */
		public function acceptsAnyContentType(): bool {
			if(null === ($header = $this->header('Accept'))) {
				return false;
			}
			
			$accepts = explode(',', $header);
			
			return !isset($accepts[0]) || (('*' === $accepts[0]) || ('*/*' === $accepts[0]));
		}
		
		/**
		 * Determine if the request wants a JSON response
		 * @return bool
		 */
		public function wantsJson(): bool {
			return (
				$this->isAjax() && $this->acceptsAnyContentType()
			) || $this->acceptsJson();
		}
		
		/**
		 * Determine if the request client can accept a JSON response
		 * @return bool
		 */
		public function acceptsJson(): bool {
			if(null === ($header = $this->header('Accept'))) {
				return false;
			}
			
			$accepts = explode(',', $header);
			
			return isset($accepts[0]) && (str_contains($accepts[0], '/json') || str_contains($accepts[0], '+json'));
		}
		
		/**
		 * Determine if the request is an AJAX request
		 * @return bool
		 */
		public function isAjax(): bool {
			return 'XMLHttpRequest' === $this->header('X-Requested-With');
		}
		
		/**
		 * Determine if the request is a JSON request
		 * @return bool
		 */
		public function isJson(): bool {
			if(null === ($content_type = $this->header('Content-Type'))) {
				return false;
			}
			
			return str_contains($content_type, '/json') || str_contains($content_type, '+json');
		}
	}