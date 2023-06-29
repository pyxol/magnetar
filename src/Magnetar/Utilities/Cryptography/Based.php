<?php
	declare(strict_types=1);
	
	namespace Magnetar\Utilities\Cryptography;
	
	use Magnetar\Utilities\Str;
	
	class Based {
		/**
		 * Encode a raw string into a semi-safe base64-like string
		 * @param string $str Raw string
		 * @return string
		 */
		public static function based_string_encode(string $str): string {
			$string = base64_encode($str);
			$string = trim($string, '=');
			$string = strrev($string);
			$string = Str::flipCase($string);
			
			return $string;
		}
		
		/**
		 * Decode a based_string_encode()'d string into its raw version. Returns false on error, or decoded string
		 * @param string $str Encoded string from based_string_encode()
		 * @return string|false
		 */
		public static function based_string_decode(string $str): string|false {
			$string = Str::flipCase($str);
			$string = strrev($string);
			//$string = trim($string, "=");
			$string = base64_decode($string);
			
			return $string;
		}
	}