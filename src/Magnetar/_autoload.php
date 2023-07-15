<?php
	declare(strict_types=1);
	
	use Magnetar\Helpers\Env;
	//use Magnetar\Helpers\View;
	
	if(!function_exists('env')) {
		/**
		 * Get an environment variable
		 * @param string $key
		 * @param mixed $default
		 * @return mixed
		 */
		function env(string $key, mixed $default): mixed {
			return Env::get($key, $default);
		}
	}
	
	if(!function_exists('tpl')) {
		/**
		 * Get an environment variable
		 * @param string $key
		 * @param mixed $default
		 * @return mixed
		 */
		function tpl(string $key, mixed $default): mixed {
			throw new Exception("View Facade is not yet implemented");
			
			//return View::tpl($key, $default);
		}
	}