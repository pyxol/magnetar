<?php
	namespace Magnetar;
	
	class Config {
		private static $store = array();
		private static $delimiter = ".";
		
		public static function set_delimiter($new_delimiter) {
			self::$delimiter = $new_delimiter;
		}
		
		public static function set($values) {
			if(is_array($values)) {
				self::$store = array_merge(self::$store, $values);
			}
		}
		
		public static function get($key, $default_value=null) {
			$value = array();
			$keys = explode(self::$delimiter, $key);
			
			$next_key = array_shift($keys);
			
			if(!array_key_exists($next_key, self::$store)) {
				return $default_value;
			}
			
			$value = self::$store[ $next_key ];
			
			while($next_key = array_shift($keys)) {
				if(!array_key_exists($next_key, $value)) {
					return $default_value;
				}
				
				$value = $value[ $next_key ];
			}
			
			return $value;
		}
	}