<?php
	namespace Magnetar;
	
	abstract class Abstract_Cache {
		abstract public function __construct();
		
		abstract protected function get($key, $callback=false, $timeout=0);
		abstract protected function set($key, $value, $timeout=0);
		abstract protected function delete($key);
	}