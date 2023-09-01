<?php
	declare(strict_types=1);
	
	namespace Magnetar\Http;
	
	class HeaderCollection {
		protected array $headers = [];
		
		/**
		 * Add header
		 * @param string $header The header to add
		 * @param bool|int $replace Whether to replace an existing header with the same name
		 * @param int|null $response_code The HTTP response code to send
		 */
		public function add(
			string $header_key,
			string $header_value='',
			bool|int $replace=true,
			int|null $response_code=0
		): self {
			if('' === ($header_key = $this->sanitizeHeaderKey($header_key))) {
				return $this;
			}
			
			$this->headers[] = [
				'header' => $header_key,
				'value' => $this->sanitizeHeaderValue($header_value),
				'replace' => $replace,
				'response_code' => $response_code
			];
			
			// @TODO add validation support
			// @TODO add sanitization (trim, lowercase header name, etc)
			// @TODO utilize replace logic in add()
			
			return $this;
		}
		
		/**
		 * Send all headers
		 * @return void
		 */
		public function send(): void {
			foreach($this->headers as $header) {
				header($header['header'] .':'. $header['value'], $header['replace'], $header['response_code']);
			}
		}
		
		/**
		 * Get all headers
		 * @return array
		 */
		public function all(): array {
			return $this->headers;
		}
		
		/**
		 * Sanitize a header key (everything before the header's first colon)
		 * @param string $header_key
		 * @return string
		 */
		public function sanitizeHeaderKey(string $header_key): string {
			return strtolower(trim($header_key));
		}
		
		/**
		 * Sanitize a header value (everything after the header's first colon)
		 * @param string $header_value
		 * @return string
		 */
		public function sanitizeHeaderValue(string $header_value): string {
			return trim($header_value);
		}
		
		/**
		 * Get a header by name
		 * @param string $name The header name
		 * @return string|null
		 */
		public function get(string $name): ?string {
			// sanitize
			if('' === ($name = $this->sanitizeHeaderKey($name))) {
				return null;
			}
			
			foreach($this->headers as $header) {
				if($header['header'] === $name) {
					return $header['value'];
				}
			}
			
			return null;
		}
		
		/**
		 * Remove a header by name
		 * @param string $name The header name
		 * @return self
		 */
		public function remove(string $name): self {
			// sanitize
			if('' === ($name = $this->sanitizeHeaderKey($name))) {
				return $this;
			}
			
			foreach($this->headers as $key => $header) {
				if($header['header'] === $name) {
					unset($this->headers[ $key ]);
				}
			}
			
			return $this;
		}
		
		/**
		 * Check if a header exists
		 * @param string $name The header name
		 * @return bool
		 */
		public function has(string $name): bool {
			// sanitize
			if('' === ($name = $this->sanitizeHeaderKey($name))) {
				return false;
			}
			
			foreach($this->headers as $header) {
				if($header['header'] === $name) {
					return true;
				}
			}
			
			return false;
		}
		
		/**
		 * Clear all headers
		 * @return self
		 */
		public function clear(): self {
			$this->headers = [];
			
			return $this;
		}
	}