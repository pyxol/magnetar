<?php
	declare(strict_types=1);
	
	namespace Magnetar\Encryption;
	
	use Magnetar\Helpers\ServiceProvider;
	use Magnetar\Encryption\Encryption;
	
	/**
	 * Provider for data encryption services
	 */
	class EncryptionServiceProvider extends ServiceProvider {
		/**
		 * {@inheritDoc}
		 */
		public function register(): void {
			// register connection services
			$this->app->singleton('encryption', function() {
				return new Encryption(
					$this->app->config['app.key'],
					$this->app->config['app.digest'] ?? 'sha256',
					$this->app->config['app.cipher']
				);
			});
		}
		
		/**
		 * {@inheritDoc}
		 */
		public function provides(): array {
			return [
				'encryption',
			];
		}
	}