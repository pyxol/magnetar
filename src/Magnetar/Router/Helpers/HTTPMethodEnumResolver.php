<?php
	declare(strict_types=1);
	
	namespace Magnetar\Router\Helpers;
	
	use Exception;
	
	use Magnetar\Router\Enums\HTTPMethodEnum;
	
	/**
	 * A helper class for resolving HTTP method strings to HTTPMethod enums and vice versa
	 */
	class HTTPMethodEnumResolver {
		/**
		 * Convert an HTTP method string to an HTTPMethod enum
		 * @param string $method The HTTP method string. All caps is expected
		 * @return HTTPMethod The HTTPMethod enum
		 * 
		 * @throws Exception If the method is unknown and no default value is provided
		 * 
		 * @see \Magnetar\Router\Enums\HTTPMethodEnum
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
				'CONNECT' => HTTPMethodEnum::CONNECT,
				default => $default ?? throw new Exception('Unknown HTTP method: '. $method)
			};
		}
		
		/**
		 * Resolve an HTTPMethod enum to the string version. Returns $default if the method is unknown
		 * @param HTTPMethodEnum $method The HTTPMethod enum
		 * @param mixed $default The default value to return if the method is unknown
		 * @return mixed The string version of the HTTP method
		 */
		public static function resolveToString(HTTPMethodEnum $method, mixed $default=null): mixed {
			switch($method) {
				case HTTPMethodEnum::GET:
					return 'GET';
				case HTTPMethodEnum::POST:
					return 'POST';
				case HTTPMethodEnum::PUT:
					return 'PUT';
				case HTTPMethodEnum::PATCH:
					return 'PATCH';
				case HTTPMethodEnum::DELETE:
					return 'DELETE';
				case HTTPMethodEnum::OPTIONS:
					return 'OPTIONS';
				case HTTPMethodEnum::HEAD:
					return 'HEAD';
				case HTTPMethodEnum::CONNECT:
					return 'CONNECT';
				default:
					return $default;
			}
		}
	}