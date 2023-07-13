<?php
	declare(strict_types=1);
	
	namespace Magnetar\Bootstrap;
	
	use Magnetar\Bootstrap\BootstrapLoaderInterface;
	use Magnetar\Application;
	use Magnetar\Config\Config;
	
	class LoadConfigs implements BootstrapLoaderInterface {
		public function bootstrap(Application $app): void {
			// load config
			$app->instance('config', $config = new Config([]));
			
			$this->loadConfigFiles($app, $config);
			
			// default PHP settings
			date_default_timezone_set($config->get('app.timezone', 'UTC'));
			
			mb_internal_encoding($config->get('app.internal_encoding', 'UTF-8'));
		}
		
		/**
		 * Load the configuration items from all of the files.
		 * @param Application $app
		 * @param Config $config
		 * @return void
		 */
		protected function loadConfigFiles(Application $app, Config $config): void {
			$files = $this->getConfigFiles($app);
			
			foreach($files as $file) {
				$config->set(basename($file, '.php'), require $file);
			}
		}
		
		/**
		 * Get all of the configuration files for the application.
		 * @param Application $app
		 * @return array
		 */
		protected function getConfigFiles(Application $app): array {
			$config_dir = $app->basePath('config') . DIRECTORY_SEPARATOR .'*.php';
			
			if(false === ($raw_files = glob($app->basePath('config') . DIRECTORY_SEPARATOR .'*.php'))) {
				return [];
			}
			
			$files = [];
			
			foreach($raw_files as $file) {
				$files[ basename($file, '.php') ] = $file;
			}
			
			ksort($files, SORT_NATURAL);
			
			return $files;
		}
	}