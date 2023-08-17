<?php
	declare(strict_types=1);
	
	namespace Magnetar\Router;
	
	use Magnetar\Helpers\ServiceProvider;
	use Magnetar\Router\Router;
	
	class RouterServiceProvider extends ServiceProvider {
		/**
		 * Register the service provider
		 * @return void
		 */
		public function register(): void {
			$this->registerRouter();
			$this->registerUrlGenerator();
		}
		
		/**
		 * Registers the Router singleton
		 * @return void
		 */
		protected function registerRouter(): void {
			$this->app->singleton('router', function($app) {
				return new Router($app);
			});
		}
		
		/**
		 * Registers the URLGenerator singleton
		 * @return void
		 */
		protected function registerUrlGenerator(): void {
			$this->app->singleton('urlgenerator', function($app) {
				return new URLGenerator($app);
			});
		}
	}