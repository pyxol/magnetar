<?php
	declare(strict_types=1);
	
	namespace Magnetar\Router;
	
	use Magnetar\Router\Router;
	
	class RouterFileLoader {
		/**
		 * constructor
		 * @param Router $router
		 */
		public function __construct(
			protected Router $router
		) {
			
		}
		
		/**
		 * Load routes from a file
		 * @param string $file_path
		 * @return void
		 */
		public function loadFrom(string $file_path): void {
			require $file_path;
		}
	}