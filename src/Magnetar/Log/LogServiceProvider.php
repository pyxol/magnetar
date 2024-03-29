<?php
	declare(strict_types=1);
	
	namespace Magnetar\Log;
	
	use Magnetar\Helpers\ServiceProvider;
	use Magnetar\Log\Logger;
	
	/**
	 * Registers the logger service
	 */
	class LogServiceProvider extends ServiceProvider {
		/**
		 * {@inheritDoc}
		 */
		public function register(): void {
			// register connection services
			$this->app->singleton('logger', fn ($app) => new Logger($app));
		}
	}