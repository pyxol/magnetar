<?php
	declare(strict_types=1);
	
	namespace Magnetar\Utilities\Cryptography;
	
	use Magnetar\Utilities\Str;
	
	/**
	 * Scramble utility static class
	 */
	class Scramble {
		/**
		 * Encode a string into a base64-like string that is non-trivial to decode
		 * @param string $str String to encode
		 * @return string
		 */
		public static function encode(string $str): string {
			$string = base64_encode($str);
			$string = trim($string, '=');
			$string = strrev($string);
			return Str::flipCase($string);
		}
		
		/**
		 * Decode a string produced by the Scramble::encode() method
		 * @param string $str Encoded string from Scramble::encode()
		 * @return string|false
		 */
		public static function decode(string $str): string|false {
			$string = Str::flipCase($str);
			$string = strrev($string);
			//$string = trim($string, '=');
			return base64_decode($string);
		}
	}