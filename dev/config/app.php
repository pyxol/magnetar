<?php
	use Magnetar\Helpers\Facades\Facade;
	use Magnetar\Helpers\ServiceProvider;
	
	return [
		// timezone
		'timezone' => 'US/Central',
		
		// internal encoding charset (mb_insertnal_encoding)
		'internal_encoding' => 'UTF-8',
		
		// environment ("dev" or "prod")
		'env' => 'dev',
		
		// aliases to load
		'aliases' => Facade::defaultAliases(),
		
		// service providers to load
		'providers' => ServiceProvider::defaultProviders()->toArray(),
	];