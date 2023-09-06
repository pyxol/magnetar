<?php
	declare(strict_types=1);
	
	namespace Magnetar\Cache;
	
	use Magnetar\Helpers\ServiceProvider;
	use Magnetar\Helpers\DeferrableServiceInterface;
	use Magnetar\Cache\StoreManager;
	
	/**
	 * Provider for cache services
	 */
	class CacheServiceProvider extends ServiceProvider implements DeferrableServiceInterface {
		/**
		 * {@inheritDoc}
		 */
		public function register(): void {
			// register connection services
			$this->app->singleton('cache', function() {
				return new StoreManager($this->app);
			});
		}
		
		/**
		 * {@inheritDoc}
		 */
		public function provides(): array {
			return [
				'cache',
				//StoreManager::class
			];
		}
	}