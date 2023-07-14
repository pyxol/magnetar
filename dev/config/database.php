<?php
	return [
		'default' => 'mariadb',
		
		'connections' => [
			'mariadb' => [
				'host' => env('DB_HOST', 'localhost'),
				'port' => env('DB_PORT', '3306'),
				'database' => env('DB_DATABASE', 'db'),
				'user' => env('DB_USER', 'user'),
				'password' => env('DB_PASSWORD', 'password'),
				'charset' => env('DB_CHARSET', 'utf8mb4'),
			],
		],
	];