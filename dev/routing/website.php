<?php
	declare(strict_types=1);
	
	use Magnetar\Helpers\Facades\Router;
	
	Router::get(
		'/^cache\/set\/?$/i',
		[HomeController::class, 'set_cache']
	);
	
	Router::get(
		'/^cache\/get\/?$/i',
		[HomeController::class, 'get_cache']
	);
	
	Router::get(
		'/^db\/?$/i',
		[HomeController::class, 'db']
	);
	
	Router::get(
		'/^phpinfo\/?$/i',
		[HomeController::class, 'phpinfo']
	);
	
	Router::get(
		'/^\/?$/i',
		[HomeController::class, 'index']
	);