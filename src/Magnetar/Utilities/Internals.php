<?php
	declare(strict_types=1);
	
	namespace Magnetar\Utilities;
	
	/**
	 * PHP Internals utility static class
	 */
	class Internals {
		/**
		 * Get the basename of a namespaced class
		 * @param string $class_name The class name (including namespace)
		 * @return string The base class name
		 */
		public static function basename_class(string $class_name): string {
			$bits = explode('\\', $class_name);
			
			return end($bits);
		}
		
		/**
		 * Get the base class name of a namespaced class from an instance
		 * @param mixed $instance The instance to get the class name from
		 * @return string The base class name
		 */
		public static function class_basename_instance(mixed $instance): string {
			return self::basename_class(get_class($instance));
		}
	}