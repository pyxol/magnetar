<?php
	use Magnetar\Helpers\Facades\Facade;
	
	return [
		// timezone
		'timezone' => 'US/Central',
		
		// internal encoding charset (mb_insertnal_encoding)
		'internal_encoding' => 'UTF-8',
		
		// environment ("dev" or "production")
		'env' => 'dev',
		
		// aliases to load
		'aliases' => Facade::defaultAliases(),
	];