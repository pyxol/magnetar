<?php
	use Magnetar\Helpers\Facades\Facade;
	use Magnetar\Helpers\ServiceProvider;
	
	// @TMP
	require_once(__DIR__ .'/../app/ServiceProviders/AppServiceProvider.php');
	
	return [
		// application name
		'name' => env('APP_NAME', 'Magnetar Dev'),
		
		// environment ("dev" or "production")
		'env' => env('APP_ENV', 'production'),
		
		// timezone
		'timezone' => 'UTC',
		
		// internal encoding charset (mb_internal_encoding)
		'internal_encoding' => 'UTF-8',
		
		// aliases to load
		'aliases' => Facade::defaultAliases()->merge([
			// additional Facade aliases
			// ...
		])->toArray(),
		
		// service providers
		'providers' => ServiceProvider::defaultProviders()->merge([
			// additional default Service Providers
			App\ServiceProviders\AppServiceProvider::class,
		])->toArray(),
	];