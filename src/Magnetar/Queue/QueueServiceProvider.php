<?php
	declare(strict_types=1);
	
	namespace Magnetar\Queue;
	
	use Magnetar\Helpers\ServiceProvider;
	use Magnetar\Queue\QueueManager;
	
	/**
	 * Registers the logger service
	 */
	class QueueServiceProvider extends ServiceProvider {
		/**
		 * {@inheritDoc}
		 */
		public function register(): void {
			// register connection services
			$this->app->singleton('queue', fn ($app) => new QueueManager($app));
		}
	}