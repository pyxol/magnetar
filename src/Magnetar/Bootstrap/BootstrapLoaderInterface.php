<?php
	declare(strict_types=1);
	
	namespace Magnetar\Bootstrap;
	
	use Magnetar\Application;
	
	interface BootstrapLoaderInterface {
		/**
		 * Load the bootstrap file
		 * @param Application $app
		 * @return void
		 */
		public function bootstrap(Application $app): void;
	}