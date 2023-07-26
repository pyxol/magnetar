<?php
	declare(strict_types=1);
	
	use Magnetar\Helpers\Facades\Config;
	use Magnetar\Helpers\Env;
	
	if(!function_exists('config')) {
		/**
		 * Get a config value
		 * @param string $key Config key
		 * @param mixed $default Optional. Value to return if the key is not found. Defaults to empty string
		 * @return mixed
		 */
		function config(string $key, mixed $default=''): mixed {
			return Config::get($key, $default);
		}
	}
	
	if(!function_exists('env')) {
		/**
		 * Get an environment variable
		 * @param string $key Environment variable key
		 * @param mixed $default Default value to return if the key is not found
		 * @return mixed
		 */
		function env(string $key, mixed $default): mixed {
			return Env::get($key, $default);
		}
	}