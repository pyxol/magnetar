<?php
	declare(strict_types=1);
	
	namespace Magnetar\Bootstrap;
	
	use Magnetar\Application;
	use Magnetar\Helpers\Facade\Facade;
	
	class RegisterFacades implements BootstrapLoaderInterface {
		public function bootstrap(Application $app): void {
			Facade::clearResolvedInstances();   // reset any resolved instances
			
			Facade::setFacadeApplication($app);
		}
	}