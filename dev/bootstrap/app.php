<?php
	declare(strict_types=1);
	
	use Magnetar\Application;
	use Magnetar\Kernel\Http\Kernel;
	
	$app = new Application(
		dirname(__DIR__)
	);
	
	$app->singleton(Kernel::class, function($app) {
		return new Kernel($app);
	});
	
	return $app;