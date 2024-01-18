<?php
	declare(strict_types=1);
	
	namespace Magnetar\Router;
	
	use Magnetar\Application;
	use Magnetar\Router\URLBuilder;
	
	/**
	 * URL generator
	 */
	class URLGenerator {
		public function __construct(
			/**
			 * The application instance
			 * @var Application
			 */
			protected Application $app
		) {
			
		}
		
		/**
		 * Generate a URL from a path and parameters
		 * @param string $path The path to use
		 * @param array $params The parameters to use
		 * @return string
		 */
		public function to(string $path, array $params=[]): string {
			return (string)(new URLBuilder($this->app))->path($path)->params($params);
		}
		
		/**
		 * Generate a URLBuilder instance from a URL
		 * @param string $url The URL to use
		 * @return \Magnetar\Router\URLBuilder
		 */
		public function from(string $url): URLBuilder {
			return (new URLBuilder($this->app))->make($url);
		}
	}