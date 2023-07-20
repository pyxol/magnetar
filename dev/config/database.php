<?php
	return [
		'default' => env('DB_CONNECTION', 'mariadb'),
		
		'connections' => [
			'mariadb' => [
				'adapter' => 'mariadb',
				'host' => env('DB_HOST', 'localhost'),
				'port' => env('DB_PORT', '3306'),
				'database' => env('DB_DATABASE', 'db'),
				'user' => env('DB_USER', 'user'),
				'password' => env('DB_PASSWORD', 'password'),
				'charset' => env('DB_CHARSET', 'utf8mb4'),
			],
			'sqlite' => [
				'adapter' => 'sqlite',
				'database' => env('DB_DATABASE', 'database.sqlite3'),
			],
		],
	];