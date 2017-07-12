<?php
	namespace Magnetar;
	
	class Template {
		private $debug;
		private $path;
		private $vars = array();
		
		public function __construct() {
			$tpl_path = Config::get("Template.path");
			
			if(!empty($tpl_path)) {
				$this->path = rtrim($tpl_path, "/\\") . DIRECTORY_SEPARATOR;
			} else {
				throw new Exception("Template.path configuration key not set.");
			}
			
			$this->debug = Config::get("Template.debug", false);
		}
		
		private function clean_tpl_name($tpl) {
			$tpl = str_replace("\\", "/", $tpl);
			$tpl = str_replace("../", "", $tpl);
			$tpl = ltrim($tpl, " /");
			
			if(false !== stripos($tpl, ".php")) {
				$tpl = preg_replace("#\.php$#si", "", $tpl);
			}
			
			return $tpl;
		}
		
		public function view($tpl) {
			$tpl = $this->clean_tpl_name($tpl);
			
			if($tpl) {
				if(false === include($this->path . $tpl .".php")) {
					if($this->debug) {
						print "Template '". $tpl ."' not found or errored.";
					}
				}
			}
			
			return $this;
		}
		
		public function set($name, $value=null) {
			if(is_array($name)) {
				foreach($name as $_name => $_value) {
					$this->vars[ $_name ] = $_value;
				}
			} else {
				$this->vars[ $name ] = $value;
			}
			
			return $this;
		}
		
		public function get($name) {
			if(isset($this->vars[ $name ])) {
				return $this->vars[ $name ];
			}
		}
		
		public function __set($name, $value) {
			$this->vars[ $name ] = $value;
		}
		
		public function __get($name) {
			if(isset($this->vars[ $name ])) {
				return $this->vars[ $name ];
			} else {
				return null;
			}
		}
		
		public function __isset($name) {
			return isset($this->vars[ $name ]);
		}
		
		public function __unset($name) {
			unset($name);
			
			return $this;
		}
	}