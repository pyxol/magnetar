<?php
	namespace Magnetar;
	
	class Request {
		private static $config;
		private static $vars;
		private static $url;
		private static $base_url;
		private static $method;
		
		public function __construct() {
			self::$config	= Config::get('site', array());
			
			self::$vars		= $_REQUEST;
			self::$base_url	= (isset(self::$config['schema'])?self::$config['schema']:"http") ."://". (isset(self::$config['domain'])?self::$config['domain']:getenv("HTTP_HOST"));
			self::$url		= self::$base_url . getenv("REQUEST_URI");
			self::$method	= getenv("REQUEST_METHOD");
		}
		
		public function setUrl($url, $redirect_on_diff=true) {
			if(!preg_match("#^https?\:\/\/#si", $url)) {
				$url = self::$base_url ."/". ltrim($url, "/");
			}
			
			self::$url = $url;
			
			if($redirect_on_diff && (self::$url !== self::$base_url . getenv("REQUEST_URI"))) {
				$this->redirect(self::$url);
			}
		}
		
		public function getUrl() {
			return self::$url;
		}
		
		public function redirect($url, $status=301) {
			if(!headers_sent()) {
				header("Redirect: ". $url, true, $status);
				
				die;
			} else {
				print "<meta http-equiv=\"refresh\" content=\"0; url=". $url ."\">\n";
			}
		}
		
		public function get($name, $else_return=null) {
			if(isset(self::$vars[ $name ])) {
				return self::$vars[ $name ];
			} else {
				return $else_return;
			}
		}
		
		
		// Static methods
		
		public static function isAjaxRequest() {
			return ("xmlhttprequest" === strtolower(getenv("HTTP_X_REQUESTED_WITH")));
		}
		
		public static function setContentType($content_type="text/html") {
			header("Content-Type: ". $content_type, true);
		}
		
		public static function setNoCache() {
			header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
			header("Last-Modified: ". gmdate("D, d M Y H:i:s") ." GMT");
			header("Cache-Control: no-store, no-cache, must-revalidate");
			header("Cache-Control: post-check=0, pre-check=0", false);
			header("Pragma: no-cache");
		}
		
		
		
		
		// Magic methods
		
		public function __get($name) {
			return (isset(self::$vars[ $name ])?self::$vars[ $name ]:null);
		}
		
		//public function __set($name, $value) {
		//	self::$vars[ $name ] = $value;
		//}
		
		public function __isset($name) {
			return isset(self::$vars[ $name ]);
		}
		
		//public function __unset($name) {
		//	unset(self::$vars[ $name ]);
		//}
	}