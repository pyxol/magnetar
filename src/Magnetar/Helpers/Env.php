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
				//static::$store = [];
				static::$store = $_ENV;
			}
			
			return static::$store;
		}
		
		/**
		 * Get an environment variable. Returns $default if not found
		 * @param string $key
		 * @param mixed $default
		 * @return mixed
		 */
		public static function get(string $key, mixed $default=null): mixed {
			return static::getStore()[ $key ] ?? $default;
		}
	}