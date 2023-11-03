<?php
	declare(strict_types=1);
	
	namespace Magnetar\Http;
	
	use Magnetar\Http\Response;
	
	class JsonResponse extends Response {
		/**
		 * Constructor method
		 * 
		 * @note Sets the default Content-Type header to application/json
		 */
		public function __construct() {
			parent::__construct();
			
			// set default content type to JSON
			$this->header('Content-Type', 'application/json');
		}
		
		/**
		 * Set the response data. This will be encoded to JSON
		 * @param mixed $body
		 * @return self
		 * 
		 * @note Intentionally overrides the json method in the Response class
		 */
		public function json(mixed $data): self {
			return $this->body( json_encode($data) );
		}
		
		/**
		 * Set the raw JSON response body. Expects a JSON string
		 * @param string $json The JSON string to set as the response body
		 * @return self
		 */
		public function rawJson(string $json): self {
			return $this->body($json);
		}
	}