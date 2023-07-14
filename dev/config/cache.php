<?php
	return [
		'default' => env('CACHE_TYPE', 'inmemory'),
		
		'connections' => [
			'memcached' => [
				'host' => env('CACHE_HOST', 'localhost'),
				'port' => env('CACHE_PORT', '11211'),
			],
		],
	];