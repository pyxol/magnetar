<?php
	declare(strict_types=1);
	
	namespace Magnetar\Auth;
	
	use Magnetar\Helpers\ServiceProvider;
	use Magnetar\Helpers\DeferrableServiceInterface;
	use Magnetar\Auth\AuthManager;
	
	/**
	 * Auth service provider
	 */
	class AuthServiceProvider extends ServiceProvider implements DeferrableServiceInterface {
		/**
		 * {@inheritDoc}
		 */
		public function register(): void {
			// register connection services
			$this->app->singleton('auth', fn ($app) => new AuthManager($app));
		}
		
		/**
		 * {@inheritDoc}
		 */
		public function provides(): array {
			return [
				AuthManager::class,
			];
		}
	}