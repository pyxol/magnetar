<?php
	namespace Magnetar;
	
	/*
		Config Settings:
			Cache.Memcache.servers = [
				{'host': "127.0.0.1"[, 'port': 11211]}
			]
		
	*/
	
	
	class Cache_Memcache extends Abstract_Cache {
		private $handler;
		
		const SET_FLAG = MEMCACHE_COMPRESSED;
		
		public function __construct() {
			if(!class_exists('Memcache')) {
				throw new \Exception("Required class 'Memcache' not found.");
			}
			
			$this->handler = new \Memcache();
			
			$servers = Config::get('Cache.Memcache.servers');
			
			if(empty($servers)) {
				throw new \Exception("Config 'Cache.Memcache.servers' is required yet empty.");
			}
			
			foreach($servers as $server) {
				$this->handler->addServer($server['host'], (!empty($server['port'])?$server['port']:11211));
			}
			
			return $this;
		}
		
		public function get($key, $callback=null, $expire=0) {
			if(is_array($key)) {
				// handle array
				
				// if cache is missing for some/all keys, callable callback is sent
				//    a single parameter: a simple array of missing cache keys and EXPECTS
				//    back an array with missing cache keys => value for each missing cache key
				
				$values = $this->handler->get($key);
				
				if((null !== $callback) && (count($values) !== count($key))) {
					$missing_keys = array_diff($key, array_keys($values));
					
					if(count($missing_keys)) {
						if(is_callable($callback)) {
							$added_values = call_user_func_array($callback, array($missing_keys));
						} else {
							$added_values = array_fill_keys($missing_keys, $callback);
						}
						
						foreach($added_values as $_key => $_value) {
							$this->set($_key, $_value, $expire);
							
							$values[ $_key ] = $_value;
						}
					}
				}
				
				return $values;
			}
			
			if((false === ($value = $this->handler->get($key))) && (null !== $callback)) {
				if(is_callable($callback)) {
					$value = call_user_func($callback);
				} else {
					$value = $callback;
				}
				
				$this->set($key, $value, $expire);
			}
			
			return $value;
		}
		
		public function set($key, $value, $expire=0) {
			return $this->handler->set($key, $value, self::SET_FLAG, $expire);
		}
		
		public function delete($key) {
			$this->handler->delete($key);
		}
		
		// talk directly to the handler
		public function __call($name, $arguments) {
			if(method_exists($this->handler, $name)) {
				call_user_func_array(array($this->handler, $name), $arguments);
			} else {
				throw new \Exception("Cache method '". $name ."' doesn't exist.");
			}
		}
	}