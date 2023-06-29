<?php
	// Cache Engine
	/////////////////
	
	
	class Cache {
		private $_log				= array();			// array for storing a log of class events
		
		private $_cache_active		= false;			// cache engine status
		private $_cache_dir 		= false;			// cache storage directory
		private $_cache_ext			= "dcf";			// distynct cache file
		private $_cache_divider		= ";";				// character between cache expiry date and cache value in actual cache file
		
		private $_date_syntax		= "YmdHis";		// php's date() syntax for cache processor datetime
		private $_time				= time();		// datetime of cache processor
		private $_expiry_default	= (15 * 60);	// default expiration length = 15 minutes
		private $_expiry_infinite	= (30 * 365 * 24 * 60 * 60);	// infinite expiration length = +30 years [yea, no consideration for leap years, big whoop, wannafightaboutit]
		
		function __construct($cache_dir=false) {
			if(!empty($cache_dir)) {
				$real_path = rtrim(realpath($cache_dir), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
				
				if(is_dir($real_path)) {
					$this->_cache_dir = $real_path;
					
					$this->_cache_active = true;
				}
			}
		}
		
		public function __destruct() {
			$this->_cache_dir = false;
			$this->_cache_active = true;
		}
		
		
		
		
		// internal stuff
		
		private function _is_active() {
			return ($this->_cache_active === true);
		}
		
		private function _to_hash($string) {
			return md5($string);
		}
		
		private function _name_to_hash_file($name) {
			if(empty($name)) {
				return false;
			}
			
			$hash = $this->_to_hash($name);
			
			return $this->_cache_dir . $hash .".". $this->_cache_ext;
		}
		
		
		// public functionality
		
		public function purge($name=false) {
			if(!$this->_is_active()) {
				return false;
			}
			
			$cache_file_path = $this->_name_to_hash_file($name);
			
			if(!empty($cache_file_path)) {
				if(file_exists($cache_file_path)) {
					unlink($cache_file_path);
					
					return true;
				}
			}
			
			return false;
		}
		
		public function get($name=false) {
			if(!$this->_is_active()) {
				return false;
			}
			
			$cache_file_path = $this->_name_to_hash_file($name);
			
			if(!empty($cache_file_path)) {
				if(file_exists($cache_file_path)) {
					$cache_contents = "";
					
					// open up cache file and see what's up
					$handler = fopen($cache_file_path, "r");
					
					$cache_contents = fread($handler, 9);	// 8 for datetime + 1 for divider character
					
					if(preg_match("#^([0-9]{8})#", $cache_contents, $cache_match)) {
						if($cache_match[1] >= date($this->_date_syntax)) {
							$cache_contents = "";	// reset the contents variable
							
							while(!feof($handler)) {
								$cache_contents .= fread($handler, 1024);
							}
							
							if(!empty($cache_contents)) {
								fclose($handler);
								
								$cache_value = unserialize($cache_contents);
								
								return $cache_contents;
							}
						}
					}
					
					fclose($handler);
				}
			}
			
			return false;
		}
		
		public function set($name, $value, $expiry=false) {
			if(!$this->_is_active()) {
				return false;
			}
			
			$cache_file_path = $this->_name_to_hash_file($name);
			
			if(empty($cache_file_path)) {
				return false;
			}
			
			if(!is_numeric($expiry)) {
				$expiry = $this->_expiry_default;
			}
			
			$value_type = gettype($value);
			
			switch($value_type) {
				case "integer":
				case "double":
				case "string":
				case "boolean":
				case "array":
				case "object":
				case "NULL":
					// break out of this switch
					break;
				
				// we can't store this stuff
				case "resource":
				case "unknown type":
				default:
					return false;
			}
			
			$cache_expire = date($this->_date_syntax, ($this->_time + $expiry));
			$cache_value = serialize($value);
			
			file_put_contents($cache_file_path, $cache_expire . $this->_cache_divider . $cache_value, LOCK_EX);
			
			return true;
		}
		
		public function enable_cache() {
			$this->_cache_active = true;
		}
		
		public function disable_cache() {
			$this->_cache_active = false;
		}
	}