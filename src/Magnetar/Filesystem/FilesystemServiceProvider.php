<?php
	declare(strict_types=1);
	
	namespace Magnetar\Filesystem;
	
	use Magnetar\Helpers\ServiceProvider;
	use Magnetar\Filesystem\FilesystemManager;
	
	class FilesystemServiceProvider extends ServiceProvider {
		/**
		 * Register the service provider
		 */
		public function register(): void {
			$this->registerFilesystemManager();
		}
		
		/**
		 * Register the filesystem manager
		 * @return void
		 */
		public function registerFilesystemManager(): void {
			$this->app->singleton('files', function($app) {
				return new FilesystemManager($app);
			});
		}
		
		
	}