<?php
	declare(strict_types=1);
	
	namespace Magnetar\Bootstrap;
	
	use Magnetar\Application;
	
	class BootServiceProviders implements BootstrapLoaderInterface {
		/**
		 * Tell the app to boot service providers
		 * @param Application $app
		 * @return void
		 */
		public function bootstrap(Application $app): void {
			$app->bootServiceProviders();
		}
	}