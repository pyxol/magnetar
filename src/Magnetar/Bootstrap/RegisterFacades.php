<?php
	declare(strict_types=1);
	
	namespace Magnetar\Bootstrap;
	
	use Magnetar\Application;
	use Magnetar\Helpers\Facades\Facade;
	use Magnetar\Helpers\AliasLoader;
	
	/**
	 * Registers the application facades
	 */
	class RegisterFacades implements BootstrapLoaderInterface {
		public function bootstrap(Application $app): void {
			Facade::clearResolvedInstances();   // reset any resolved instances
			
			Facade::setFacadeApplication($app);
			
			AliasLoader::getInstance(
				$app->make('config')->get('app.aliases', [])
			)->register();
		}
	}