<?php
	declare(strict_types=1);
	
	namespace Magnetar\Utilities;
	
	class JSON {
		/**
		 * Encode a variable into a JSON string
		 * @param mixed $var
		 * @return string
		 */
		public static function encode(mixed $var): string {
			return json_encode($var);
		}
		
		/**
		 * Decode raw JSON into a variable. Automatically assigns associated values
		 * @param string $var Raw JSON to decode
		 * @return mixed
		 */
		public static function decode(string $var): mixed {
			return json_decode($var, true);
		}
		
		/**
		 * Attempt to decode the string if it looks like JSON data. Returns decoded array or $var if not JSON data
		 * @param mixed $var Raw string that might be raw JSON data
		 * @return mixed
		 */
		public static function maybe_decode(mixed $var): mixed {
			if(!is_string($var)) {
				return $var;
			}
			
			$var = trim($var);
			
			$first_char = substr($var, 0, 1);
			$last_char = substr($var, -1);
			
			if((('[' !== $first_char) && (']' !== $last_char)) && (('{' !== $first_char) && ('}' !== $last_char))) {
				return $var;
			}
			
			$decoded = @json_decode($var, true);
			
			if(is_null($decoded)) {
				return $var;
			}
			
			return $decoded;
		}
		
		/**
		 * Attempt to encode the string if it looks like JSON data. Returns JSON string if array/object, otherwise $var
		 * @param mixed $var Raw variable to possibly encode into JSON
		 * @return mixed
		 */
		public static function maybe_encode(mixed $var): mixed {
			if(is_array($var) || is_object($var)) {
				return json_encode($var);
			}
			
			return $var;
		}
	}