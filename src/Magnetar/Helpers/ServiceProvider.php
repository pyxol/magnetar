<?php
	declare(strict_types=1);
	
	namespace Magnetar\Helpers;
	
	use Magnetar\Application;
	use Magnetar\Helpers\DeferrableServiceInterface;
	use Magnetar\Helpers\DefaultServiceProviders;
	
	/**
	 * A class that service providers should extend
	 */
	class ServiceProvider {
		public function __construct(
			/**
			 * The application instance
			 * @var Application
			 */
			protected Application $app
		) {
			
		}
		
		/**
		 * Get the services provided by the provider
		 * @return array
		 */
		public function provides(): array {
			return [];
		}
		
		/**
		 * Register the service provider with the application
		 * @return void
		 */
		public function register(): void {
			
		}
		
		/**
		 * Determine if the service provider is deferred
		 * @return bool
		 */
		public function isDeferred(): bool {
			return ($this instanceof DeferrableServiceInterface);
		}
		
		/**
		 * Get the default service providers
		 * @return DefaultServiceProviders
		 */
		public static function defaultProviders(): DefaultServiceProviders {
			return new DefaultServiceProviders;
		}
	}