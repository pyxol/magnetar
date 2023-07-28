<?php
	declare(strict_types=1);
	
	$app = new Magnetar\Application(
		dirname(__DIR__)
	);
	
	$app->singleton(
		Magnetar\Http\Kernel::class
	);
	//$app->singleton(Kernel::class, function($app) {
	//	return new Kernel($app);
	//});
	
	return $app;