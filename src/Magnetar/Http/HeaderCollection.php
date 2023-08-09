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
		public function add(string $header, bool|int $replace=true, int|null $response_code=0): self {
			$this->headers[] = [
				'header' => $header,
				'replace' => $replace,
				'response_code' => $response_code
			];
			
			// @TODO add validation support
			// @TODO add sanitization
			// @TODO utilize replace logic in add()
			
			return $this;
		}
		
		/**
		 * Send all headers
		 * @return void
		 */
		public function send(): void {
			foreach ($this->headers as $header) {
				header($header['header'], $header['replace'], $header['response_code']);
			}
		}
	}