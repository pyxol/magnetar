<?php
	declare(strict_types=1);
	
	namespace Magnetar\Database;
	
	use Magnetar\Helpers\ServiceProvider;
	use Magnetar\Database\ConnectionManager;
	
	class DatabaseServiceProvider extends ServiceProvider {
		/**
		 * {@inheritDoc}
		 */
		public function register(): void {
			// register connection services
			$this->app->singleton('db', fn () => new ConnectionManager($this->app));
			//$this->app->singleton('db', function() {
			//	return new ConnectionManager($this->app);
			//});
		}
		
		
		/**
		 * {@inheritDoc}
		 */
		public function provides(): array {
			return [
				ConnectionManager::class
			];
		}
	}