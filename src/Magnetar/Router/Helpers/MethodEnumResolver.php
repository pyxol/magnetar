<?php
	declare(strict_types=1);
	
	namespace Magnetar\Router\Helpers;
	
	use Exception;
	
	use Magnetar\Router\Enums\HTTPMethod;
	
	/**
	 * A helper class for resolving HTTP method strings to HTTPMethod enums
	 */
	class HTTPMethodEnumResolver {
		/**
		 * Convert an HTTP method string to an HTTPMethod enum
		 * @param string $method The HTTP method string
		 * @return HTTPMethod The HTTPMethod enum
		 * 
		 * @throws Exception If the method is unknown and no default value is provided
		 * 
		 * @see Magnetar\Router\Enums\HTTPMethod
		 */
		public static function resolve(string $method, mixed $default=null): HTTPMethod {
			return match(strtoupper($method)) {
				'GET' => HTTPMethod::GET,
				'POST' => HTTPMethod::POST,
				'PUT' => HTTPMethod::PUT,
				'PATCH' => HTTPMethod::PATCH,
				'DELETE' => HTTPMethod::DELETE,
				'OPTIONS' => HTTPMethod::OPTIONS,
				'HEAD' => HTTPMethod::HEAD,
				'TRACE' => HTTPMethod::TRACE,
				'CONNECT' => HTTPMethod::CONNECT,
				default => $default ?? throw new Exception('Unknown HTTP method: '. $method)
			};
		}
	}