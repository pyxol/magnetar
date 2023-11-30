<?php
	declare(strict_types=1);
	
	namespace Magnetar\Http;
	
	/**
	 * Represents a collection of HTTP headers
	 */
	class HeaderCollection {
		protected const UPPER = '_ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		protected const LOWER = '-abcdefghijklmnopqrstuvwxyz';
		
		/**
		 * Headers array
		 * @var array
		 */
		protected array $headers = [];
		
		/**
		 * Store response codes for headers that have them
		 * @var array
		 */
		protected array $response_codes = [];
		
		/**
		 * Constructor
		 */
		public function __construct(
			/**
			 * The headers to add
			 * @var array
			 */
			array $headers=[]
		) {
			foreach($headers as $key => $value) {
				$this->add($key, $value);
			}
		}
		
		/**
		 * Add header
		 * @param string $header The header to add
		 * @param bool|int $replace Whether to replace an existing header with the same name
		 * @param int|null $response_code The HTTP response code to send
		 */
		public function add(
			string $name,
			array|string $value='',
			bool|int $replace=true,
			int|null $response_code=0
		): self {
			// sanitize
			if('' === ($name_sanitized = $this->sanitizeName($name))) {
				return $this;
			}
			
			// check if header already exists, if so and we're not replacing, don't add
			if(isset($this->headers[ $name_sanitized ]) && !$replace) {
				return $this;
			}
			
			// start building header
			$this->headers[ $name_sanitized ] = [];
			
			if(is_array($value)) {
				// add each value
				foreach($value as $header_value) {
					$this->headers[ $name_sanitized ][] = $header_value;
				}
			} else {
				// set single value as array
				$this->headers[ $name_sanitized ] = [$value];
			}
			
			if(0 !== $response_code) {
				// edge-case: one could provide different response codes for each added header
				// but only the last one (that isn't the default value) is stored for each header
				$this->response_codes[ $header_sanitized ] = $response_code;
			}
			
			return $this;
		}
		
		/**
		 * Get all headers
		 * @param string|null $name If provided, only return headers with this name
		 * @return array
		 */
		public function all(string|null $name=null): array {
			if(null !== $name) {
				// return only headers with this name
				return $this->headers[ $this->sanitizeName($name) ] ?? [];
			}
			
			return $this->headers;
		}
		
		/**
		 * Get a header by name
		 * @param string $name The header name
		 * @return string|null
		 */
		public function get(string $name): ?string {
			// sanitize
			if('' === ($name_sanitized = $this->sanitizeName($name))) {
				return null;
			}
			
			if(!isset($this->headers[ $name_sanitized ])) {
				return null;
			}
			
			// return first header value
			return $this->headers[ $name_sanitized ][0];
		}
		
		/**
		 * Check if a header exists
		 * @param string $name The header name
		 * @return bool
		 */
		public function has(string $name): bool {
			// sanitize
			return isset($this->headers[ $this->sanitizeName($name) ]);
		}
		
		/**
		 * Remove a header by name
		 * @param string $name The header name
		 * @return self
		 */
		public function remove(string $name): self {
			// sanitize
			if('' === ($name_sanitized = $this->sanitizeName($name))) {
				return $this;
			}
			
			unset($this->headers[ $name_sanitized ]);
			unset($this->response_codes[ $name_sanitized ]);
			
			return $this;
		}
		
		/**
		 * Clear all headers
		 * @return self
		 */
		public function clear(): self {
			$this->headers = [];
			$this->response_codes = [];
			
			return $this;
		}
		
		/**
		 * Send all headers. Does not check if headers have already been sent
		 * @return void
		 */
		public function send(): void {
			foreach($this->headers as $key => $headers) {
				foreach($headers as $i => $value) {
					header(
						$key .': '. $value,
						!$i,
						$this->response_codes[ $key ] ?? 0
					);
				}
			}
		}
		
		/**
		 * Sanitize a header name (everything before the first colon)
		 * @param string $name The header name
		 * @return string The sanitized header name
		 */
		public function sanitizeName(string $name): string {
			return trim(strtr(
				$name,
				self::UPPER,
				self::LOWER
			));
		}
		
		/**
		 * Get the headers as a string
		 * @return string
		 */
		public function __toString(): string {
			$headers = '';
			
			foreach($this->headers as $key => $headers) {
				foreach($headers as $i => $header) {
					$headers .= $key .': '. $header ."\r\n";
				}
			}
			
			return $headers;
		}
	}