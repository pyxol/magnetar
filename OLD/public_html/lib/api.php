<?php
	class api {
		private static $instances = array();
		private static $class_keys = array();
		
		public static function __callStatic($class, $vars) {
			$class_id = (isset($vars[0])?$vars[0]:0);
			
			if(!isset(self::$instances[ $class ][ $class_id ])) {
				if(!isset(self::$instances[ $class ])) {
					self::$instances[ $class ] = array();
				}
				
				$reflection = new ReflectionClass( (isset(self::$class_keys[ $class ])?self::$class_keys[ $class ]:$class) );
				
				self::$instances[ $class ][ $class_id ] = $reflection->newInstanceArgs($vars);
				
				
				/*
				$real_class = (isset(self::$class_keys[ $class ])?self::$class_keys[ $class ]:$class);
				
				//if(!empty($vars)) {
					// use Reflection - http://php.net/reflection
					$reflection = new ReflectionClass($real_class);
					
					self::$instances[ $class ][ $class_id ] = $reflection->newInstanceArgs($vars);
				//} else {
				//	self::$instances[ $class ][ $class_id ] = new $real_class();
				//}
				*/
			}
			
			return self::$instances[ $class ][ $class_id ];
		}
		
		public static function register($key, $class_path=false) {
			if(is_array($key)) {
				foreach($key as $_key => $_class_path) {
					//if(!isset(self::$class_keys[ $_key ])) {
						self::$class_keys[ $_key ] = $_class_path;
					//}
				}
				
				return;
			}
			
			//if(!isset(self::$class_keys[ $key ])) {
				self::$class_keys[ $key ] = $class_path;
			//}
		}
		
		public static function unregister($key) {
			unset(self::$class_keys[$key]);
		}
	}