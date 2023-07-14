<?php
	return [
		'default' => env('CACHE_TYPE', 'memcached'),
		
		'connections' => [
			'memcached' => [
				'host' => env('CACHE_HOST', 'localhost'),
				'port' => env('CACHE_PORT', '11211'),
			],
		],
	];