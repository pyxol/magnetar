<?php
	declare(strict_types=1);
	
	namespace Magnetar\Http;
	
	use Magnetar\Http\Response;
	
	class JsonResponse extends Response {
		/**
		 * Set the response data. This will be encoded to JSON
		 * @param mixed $body
		 * @return self
		 */
		public function setData(mixed $data): self {
			$this->body = json_encode($data);
			
			return $this;
		}
		
		/**
		 * Set the raw JSON response body. Expects a JSON string
		 * @param string $json_body
		 * @return self
		 */
		public function setJson(string $json): self {
			$this->body = $json;
			
			return $this;
		}
		
		public function send(): self {
			$this->header('Content-Type', 'application/json; charset=UTF-8', true);
			
			$this->sendHeaders();
			$this->sendBody();
			
			$this->sent = true;
			
			return $this;
		}
	}