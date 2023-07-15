<?php
	declare(strict_types=1);
	
	namespace Magnetar\Cache;
	
	use Magnetar\Helpers\ServiceProvider;
	use Magnetar\Cache\StoreManager;
	
	class CacheServiceProvider extends ServiceProvider {
		public function register() {
			// register connection services
			$this->app->singleton('cache', function() {
				return new StoreManager($this->app);
			});
		}
	}