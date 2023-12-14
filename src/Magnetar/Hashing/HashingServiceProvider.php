<?php
	declare(strict_types=1);
	
	namespace Magnetar\Hashing;
	
	use Magnetar\Helpers\ServiceProvider;
	use Magnetar\Helpers\DeferrableServiceInterface;
	use Magnetar\Hashing\HashingManager;
	
	class HashingServiceProvider extends ServiceProvider implements DeferrableServiceInterface {
		/**
		 * Register the service provider
		 * @return void
		 */
		public function register(): void {
			$this->app->singleton('hashing', function($app) {
				return new HashingManager($app);
			});
		}
		
		/**
		 * Get the services provided by the provider
		 * @return array
		 */
		public function provides(): array {
			return [
				HashingManager::class,
				'hashing',
			];
		}
	}