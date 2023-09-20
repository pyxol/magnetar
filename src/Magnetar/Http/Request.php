<?php
	declare(strict_types=1);
	
	namespace Magnetar\Http;
	
	use Magnetar\Router\Enums\HTTPMethodEnum;
	use Magnetar\Router\Helpers\HTTPMethodEnumResolver;
	
	class Request {
		/**
		 * The requested path (without query string)
		 * @var string
		 */
		protected string $path = "";
		
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
		 * Create a new Request object
		 * @param string $path The requested path (without query string)
		 */
		public function __construct() {
			// set request method
			$this->processRequestMethod();
			
			// parse the request path and query string
			$this->processRequestPath();
			
			// record request headers
			$this->recordHeaders();
		}
		
		/**
		 * Process the request method
		 * @return void
		 */
		protected function processRequestMethod(): void {
			// set request method
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
			$path = '/'. ltrim($path, "/");
			$path = rtrim($path, '?&');
			
			$this->path = $path;
			
			if(false !== ($q_pos = strpos($this->path, '?'))) {
				// request has ?, save to request and parse parameters
				parse_str(
					substr($this->path, ($q_pos + 1)),
					$this->parameters
				);
				
				// chop off query string from request path
				$this->path = substr($this->path, 0, $q_pos);
			}
		}
		
		/**
		 * Create a new Request object
		 * @return Request
		 */
		public static function create(): Request {
			return new static();
		}
		
		/**
		 * Record request headers
		 * @return void
		 */
		protected function recordHeaders(): void {
			$this->headers = new HeaderCollection();
			
			$headers = getallheaders();
			
			if(empty($headers)) {
				return;
			}
			
			foreach($headers as $key => $value) {
				$this->headers->add($key, $value);
			}
			
			//// record request headers
			//foreach($_SERVER as $key => $value) {
			//	if(!str_starts_with($key, 'HTTP_')) {
			//		continue;
			//	}
			//	
			//	$this->headers[ $key ] = $value;
			//}
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
				if("" === ($name = strtolower(trim($name)))) {
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
			//if("" === ($name = strtolower(trim($name)))) {
			//	return $default;
			//}
			
			return $this->parameters[ $name ] ?? $default;
		}
		
		/**
		 * Get all parameters from the request
		 * @return array
		 */
		public function parameters(): array {
			return $this->parameters;
		}
		
		/**
		 * Get the raw request body
		 * @return string
		 */
		public function body(): string {
			return file_get_contents('php://input') ?: '';
		}
	}