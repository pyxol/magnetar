<?php
	require_once(__DIR__ ."/vendor/autoload.php");
	
	JungleDB\Config::set( include(__DIR__ ."/../config.php") );
	
	api::register( JungleDB\Config::get('api') );
	
	JungleDB\Router::serve();