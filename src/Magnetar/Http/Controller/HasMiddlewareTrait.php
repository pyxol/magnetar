<?php
	declare(strict_types=1);
	
	namespace Magnetar\Http\Controller;
	
	trait HasMiddlewareTrait {
		/**
		 * The middleware stack
		 * @var array
		 */
		protected array $middleware = [];
		
		/**
		 * Add middleware to the controller
		 * @param string $middleware The middleware to add
		 * @return self
		 */
		public function middleware(string $middleware): self {
			$this->middleware[] = $middleware;
			
			return $this;
		}
		
		/**
		 * Get the middleware attached to the controller
		 * @return array
		 */
		public function middlewares(): array {
			return $this->middleware;
		}
	}