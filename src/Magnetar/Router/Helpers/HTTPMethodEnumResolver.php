<?php
	declare(strict_types=1);
	
	namespace Magnetar\Router\Helpers;
	
	use Exception;
	
	use Magnetar\Router\Enums\HTTPMethodEnum;
	
	/**
	 * A helper class for resolving HTTP method strings to HTTPMethod enums
	 */
	class HTTPMethodEnumResolver {
		/**
		 * Convert an HTTP method string to an HTTPMethod enum
		 * @param string $method The HTTP method string. All caps is expected
		 * @return HTTPMethod The HTTPMethod enum
		 * 
		 * @throws Exception If the method is unknown and no default value is provided
		 * 
		 * @see Magnetar\Router\Enums\HTTPMethodEnum
		 */
		public static function resolve(string $method, mixed $default=null): HTTPMethodEnum {
			return match($method) {
				'GET'     => HTTPMethodEnum::GET,
				'POST'    => HTTPMethodEnum::POST,
				'PUT'     => HTTPMethodEnum::PUT,
				'PATCH'   => HTTPMethodEnum::PATCH,
				'DELETE'  => HTTPMethodEnum::DELETE,
				'OPTIONS' => HTTPMethodEnum::OPTIONS,
				'HEAD'    => HTTPMethodEnum::HEAD,
				'TRACE'   => HTTPMethodEnum::TRACE,
				'CONNECT' => HTTPMethodEnum::CONNECT,
				default => $default ?? throw new Exception('Unknown HTTP method: '. $method)
			};
		}
	}