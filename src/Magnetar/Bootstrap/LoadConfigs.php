<?php
	declare(strict_types=1);
	
	namespace Magnetar\Bootstrap;
	
	use Magnetar\Bootstrap\BootstrapLoaderInterface;
	use Magnetar\Application;
	use Magnetar\Config\Config;
	
	class LoadConfigs implements BootstrapLoaderInterface {
		public function bootstrap(Application $app): void {
			// @TODO not working now, eventually load config files
			$app->instance('config', new Config($app->basePath('config')));
		}
	}