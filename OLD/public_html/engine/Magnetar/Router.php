<?php
	namespace Magnetar;
	
	use \api as api;
	
	class Router {
		private static $config	= array();
		private static $request	= "";
		private static $hooks	= array();
		private static $success	= false;
		
		private static function processRule($class, $rule_params=array()) {
			$method = (isset(self::$config['method'])?self::$config['method']:"get");
			
			if(!class_exists($class)) {
				api::hook()->run('Router.route_class_not_exist');
				
				return false;
			}
			
			if(Request::isAjaxRequest()) {
				$method_ajax = (!empty(self::$config['method_ajax'])?self::$config['method_ajax']:$method ."_xhr");
				
				if(method_exists($class, $method_ajax)) {
					Request::setContentType("application/json");
					Request::setNoCache();
					
					$method = $method_ajax;
				}
			}
			
			if(!method_exists($class, $method)) {
				api::hook()->run('Router.route_class_method_not_exist');
				
				return false;
			}
			
			$class_instance = new $class();
			
			call_user_func_array(array($class_instance, $method), $rule_params);
			
			return true;
		}
		
		public static function serve() {
			api::hook()->run('Router.init');
			
			self::$config = Config::get('Router');
			
			if(!empty(self::$config['hooks'])) {
				api::hook()->add( self::$config['hooks'] );
			}
			
			$routes = (!empty(self::$config['routes'])?self::$config['routes']:false);
			
			if(!is_array($routes) || empty($routes)) {
				api::hook()->run('Router.no_routes');
				
				return;
			}
			
			$request_bits = explode("?", getenv("REQUEST_URI"), 2);
			
			self::$request = array_shift($request_bits);
			
			if(isset($routes[ self::$request ])) {
				self::$success = self::processRule($routes[ self::$request ]);
			} else {
				foreach($routes as $regex => $class) {
					if(preg_match("#^". $regex ."$#si", self::$request, $match)) {
						array_shift($match);
						
						self::$success = self::processRule($class, $match);
						
						break;
					} else {
						
					}
				}
			}
			
			if(!self::$success) {
				api::hook()->run('Router.404');
			}
			
			api::hook()->run('Router.shutdown');
			
			return;
		}
	}