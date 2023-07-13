<?php
	declare(strict_types=1);
	
	namespace Magnetar\Http;
	
	use Magnetar\Container\Container;
	use Magnetar\Router\Route;
	
	class Request {
		protected ?Route $route = null;
		
		protected string $path = "";
		protected ?string $matched_pattern = null;
		protected array $parameters = [];
		
		/**
		 * Create a new Request object
		 * @param string $path The requested path (without query string)
		 */
		public function __construct(
			protected Container $container,
			string|null $path=null
		) {
			if(is_null($path)) {
				$path = $_SERVER['REQUEST_URI'];
			}
			
			// sanitize request path
			$path = ltrim($path, "/");
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
			
			//// set base parameters (any created with URI arguments)
			//$this->parameters = $_REQUEST;
			
			//// override any existing parameters set by URI arguments
			//if(!empty($override_parameters)) {
			//	if(!is_array($override_parameters)) {
			//		//parse_str($override_parameters, $override_parameters);
			//		throw new \Exception("Request was provided invalid routed parameters");
			//	}
			//	
			//	foreach($override_parameters as $name => $value) {
			//		if("" === ($name = strtolower(trim($name)))) {
			//			continue;
			//		}
			//		
			//		$this->parameters[ $name ] = $value;
			//	}
			//}
		}
		
		/**
		 * Get the requested path
		 * @return string
		 */
		public function getPath(): string {
			return $this->path;
		}
		
		/**
		 * Pass in parameters from the route that override parameters found in URI query params (eg those found in ?...)
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
		public function getParameter(string $name, mixed $default=null): mixed {
			if("" === ($name = strtolower(trim($name)))) {
				return $default;
			}
			
			if(!isset($this->parameters[ $name ])) {
				return $default;
			}
			
			return $this->parameters[ $name ];
		}
		
		/**
		 * Get all parameters from the request
		 * @return array
		 */
		public function getParameters(): array {
			return $this->parameters;
		}
		
		/**
		 * Get the route that was matched
		 * @return Route|null
		 */
		public function getRoute(): ?Route {
			return $this->route;
		}
		
		/**
		 * Set the route that was matched
		 * @param Route $route The route that was matched
		 * @return void
		 */
		public function setRoute(Route $route): void {
			if(!is_null($this->route)) {
				return;
			}
			
			$this->route = $route;
		}
	}