<?php
	declare(strict_types=1);
	
	namespace Magnetar\Database;
	
	use Magnetar\Helpers\ServiceProvider;
	use Magnetar\Helpers\DeferrableServiceInterface;
	use Magnetar\Database\ConnectionManager;
	
	/**
	 * Database service provider
	 */
	class DatabaseServiceProvider extends ServiceProvider implements DeferrableServiceInterface {
		/**
		 * {@inheritDoc}
		 */
		public function register(): void {
			// register connection services
			$this->app->singleton('database', fn ($app) => new ConnectionManager($app));
		}
		
		/**
		 * {@inheritDoc}
		 */
		public function provides(): array {
			return [
				'database',
				ConnectionManager::class,
			];
		}
	}