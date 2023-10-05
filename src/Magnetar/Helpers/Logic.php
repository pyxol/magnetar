<?php
	declare(strict_types=1);
	
	namespace Magnetar\Helpers;
	
	class Logic {
		/**
		 * Check if a value is true-ish. Useful for Env values
		 * @param string|int|bool $value Value to check
		 * @return bool
		 */
		public static function isTrue($value): bool {
			return ($value === true) || (1 === $value) || ('1' === $value) || ('true' === strtolower($value));
		}
		
		/**
		 * Check if a value is false-ish. Useful for Env values
		 * @param string|int|bool $value Value to check
		 * @return bool
		 */
		public static function isFalse($value): bool {
			return ($value === false) || (0 === $value) || ('0' === $value) || ('false' === strtolower($value));
		}
	}