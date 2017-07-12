<?php
	namespace Magnetar;
	
	class Hook {
		private $hooks = array();
		
		//// overwrite this->hooks[$key]
		//public function set($key, $value=false) {
		//	return $this->add($key, $value, true);
		//}
		
		// add to this->hooks[$key]
		public function add($key, $value=false, $overwrite=false) {
			if($overwrite) {
				unset($this->hooks[ $key ]);
			}
			
			if(is_array($key)) {
				foreach($key as $_key => $_value) {
					if(!isset($this->hooks[ $_key ])) {
						$this->hooks[ $_key ] = array($_value);
					} else {
						$this->hooks[ $_key ][] = $_value;
					}
				}
			} elseif(is_callable($value)) {
				if(!isset($this->hooks[ $key ])) {
					$this->hooks[ $key ] = array($value);
				} else {
					$this->hooks[ $key ][] = $value;
				}
			}
			
			return $this;
		}
		
		// process this->hooks[$key], empty if $dump
		public function run($key, $dump=false) {
			if(isset($this->hooks[ $key ])) {
				foreach($this->hooks[ $key ] as $hook) {
					call_user_func($hook);
				}
				
				if($dump) {
					unset($this->hooks[ $key ]);
				}
			}
			
			return $this;
		}
	}