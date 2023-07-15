<?php
	declare(strict_types=1);
	
	namespace Magnetar\Util\Cryptography;
	
	use Magnetar\Util\Str;
	
	class Based {
		/**
		 * Encode a raw string into a semi-safe base64-like string that can be decoded using Based::decode
		 * @param string $str Raw string
		 * @return string
		 */
		public static function encode(string $str): string {
			$string = base64_encode($str);
			$string = trim($string, '=');
			$string = strrev($string);
			$string = Str::flipCase($string);
			
			return $string;
		}
		
		/**
		 * Decode a based_string_encode()'d string into its raw version. Returns false on error, or decoded string
		 * @param string $str Encoded string from Based::encode()
		 * @return string|false
		 */
		public static function decode(string $str): string|false {
			$string = Str::flipCase($str);
			$string = strrev($string);
			//$string = trim($string, "=");
			$string = base64_decode($string);
			
			return $string;
		}
	}