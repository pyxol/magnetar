<?php
	declare(strict_types=1);
	
	namespace Magnetar\Template;
	
	use Magnetar\Helpers\ServiceProvider;
	use Magnetar\Application;
	use Magnetar\Http\CookieJar\CookieJar;
	
	/**
	 * Registers the cookie jar service
	 */
	class CookieServiceProvider extends ServiceProvider {
		/**
		 * {@inheritDoc}
		 */
		public function register(): void {
			$this->app->singleton('cookie', function(Application $app) {
				$config = $app->make('config')->get('session');
				
				return (new CookieJar)->setDefaults(
					$config['expires_seconds'] ?? 3600,
					$config['path'] ?? '',
					$config['domain'] ?? ''
				);
			});
		}
		
		/**
		 * {@inheritDoc}
		 */
		//public function provides(): array {
		//	return [
		//		'cookie',
		//	];
		//}
	}