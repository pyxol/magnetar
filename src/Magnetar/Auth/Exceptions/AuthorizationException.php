<?php
	declare(strict_types=1);
	
	namespace Magnetar\Auth\Exceptions;
	
	use Exception;
	
	use Magnetar\Http\Response;
	
	/**
	 * Exception thrown when a user fails an authentication protected request
	 */
	class AuthorizationException extends Exception {
		/**
		 * Response instance
		 * @var Response
		 */
		protected ?Response $response = null;
		
		/**
		 * Set what response to return
		 * @param Response|null $response The response instance
		 * @return static
		 */
		public function respondWith(?Response $response=null): static {
			$this->response = $response;
			
			return $this;
		}
		
		/**
		 * Get the response instance
		 * @return Response|null
		 */
		public function getResponse(): ?Response {
			return $this->response;
		}
	}