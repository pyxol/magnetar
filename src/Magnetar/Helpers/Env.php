<?php
	declare(strict_types=1);
	
	namespace Magnetar\Helpers;
	
	/**
	 * Helper class to get environment variables
	 */
	class Env {
		/**
		 * Array to store environment variables
		 * @var ?array
		 */
		protected static ?array $store = null;
		
		/**
		 * Get the environment store and create it if it doesn't exist
		 * @return array
		 */
		public static function getStore(): array {
			if(null === static::$store) {
				static::$store = $_ENV;
			}
			
			return static::$store;
		}
		
		/**
		 * Get an environment variable. Returns $default if not found
		 * @param string $key Key to get
		 * @param mixed $default Default value to return if the key is not found
		 * @return mixed
		 */
		public static function get(string $key, mixed $default=null): mixed {
			return static::getStore()[ $key ] ?? $default;
		}
		
		/**
		 * Get an environment variable. Returns $default if not found
		 * @param string $key Key to get
		 * @param callable $callback Callback to call if the key is not found
		 * @return mixed
		 */
		public static function getElse(string $key, callable $callback): mixed {
			return static::getStore()[ $key ] ?? call_user_func($callback);
		}
	}