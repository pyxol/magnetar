<?php
	declare(strict_types=1);
	
	namespace Magnetar\Filesystem;
	
	use Magnetar\Helpers\ServiceProvider;
	use Magnetar\Filesystem\ConnectionManager;
	
	/**
	 * Filesystem service provider
	 */
	class FilesystemServiceProvider extends ServiceProvider {
		/**
		 * {@inheritDoc}
		 */
		public function register(): void {
			$this->registerConnectionManager();
		}
		
		/**
		 * Register the filesystem manager
		 * @return void
		 */
		public function registerConnectionManager(): void {
			$this->app->singleton('files', function($app) {
				return new ConnectionManager($app);
			});
		}
	}