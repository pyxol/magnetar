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
			
			// @TODO turn this into a manageable Provider-oriented class
			$provider_aliases = [];
			$provider_classes = $app->make('config')->get('app.providers', []);
			
			foreach($provider_classes as $provider_class) {
				$instance = new $provider_class($app);
				
				if(method_exists($instance, 'provides')) {
					$provider_aliases = array_merge($provider_aliases, $instance->provides());
				}
			}
			
			AliasLoader::getInstance(array_merge(
				$app->make('config')->get('app.aliases', []),
				$provider_aliases,
			))->register();
		}
	}