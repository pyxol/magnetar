<?php
	declare(strict_types=1);
	
	use Magnetar\Helpers\Env;
	
	if(!function_exists('env')) {
		function env(string $key, mixed $default): mixed {
			return Env::get($key, $default);
		}
	}