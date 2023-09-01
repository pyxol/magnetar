<?php
	declare(strict_types=1);
	
	namespace Magnetar\Template;
	
	use Magnetar\Helpers\ServiceProvider;
	use Magnetar\Application;
	
	class TemplateServiceProvider extends ServiceProvider {
		/**
		 * {@inheritDoc}
		 */
		public function register(): void {
			$this->app->singleton('theme', function(Application $app) {
				return new ThemeManager($app);
			});
		}
		
		/**
		 * {@inheritDoc}
		 */
		public function provides(): array {
			return [
				'theme',
				ThemeManager::class
			];
		}
	}