<?php
	declare(strict_types=1);
	
	namespace Magnetar\Filesystem;
	
	use Magnetar\Helpers\ServiceProvider;
	use Magnetar\Filesystem\ConnectionManager;
	
	/**
	 * Pipeline service provider
	 */
	class PipelineServiceProvider extends ServiceProvider {
		/**
		 * {@inheritDoc}
		 */
		public function register(): void {
			$this->app->bind('pipeline', fn ($app) => new Pipeline($app));
		}
		
		/**
		 * @{inheritDoc}
		 */
		public function provides(): array {
			return [
				'pipeline',
			];
		}
	}