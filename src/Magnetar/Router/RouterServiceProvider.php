<?php
	declare(strict_types=1);
	
	namespace Magnetar\Router;
	
	use Magnetar\Helpers\ServiceProvider;
	use Magnetar\Router\Router;
	
	class RouterServiceProvider extends ServiceProvider {
		/**
		 * Register the service provider.
		 * @return void
		 */
		public function register(): void {
			$this->registerRouter();
		}
		
		/**
		 * Registers the router singleton
		 * @return void
		 */
		protected function registerRouter(): void {
			$this->app->singleton('router', function($app) {
				return new Router($app);
			});
		}
	}