<?php
	return [
		'default' => 'mariadb',
		
		'connections' => [
			'mariadb' => [
				'host' => 'localhost',
				'dbname' => 'db',
				'user' => 'user',
				'password' => 'password',
				'charset' => 'utf8mb4',
			],
		],
		
		
		// @TMP
		'tables' => [
			'test1',
			'test2',
			'test3',
		]
	];