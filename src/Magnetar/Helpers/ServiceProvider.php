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
		/**
		 * Callbacks that should be called before the application is booted
		 * @var array
		 */
		protected array $bootingCallbacks = [];
		
		/**
		 * Callbacks that should be called once the application has booted
		 * @var array
		 */
		protected array $bootedCallbacks = [];
		
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
			// to be overridden
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
		
		/**
		 * Register a booting callback to be run before the "boot" method is called
		 * @param callable $callback The callback to be run
		 * @return void
		 */
		public function booting(callable $callback): void {
			$this->bootingCallbacks[] = $callback;
		}
		
		/**
		 * Register a booted callback to be run after the "boot" method is called
		 * @param callable $callback The callback to be run
		 * @return void
		 */
		public function booted(callable $callback): void {
			$this->bootedCallbacks[] = $callback;
		}
		
		/**
		 * Call the booting callbacks for the provider
		 * @return void
		 */
		public function callBootingCallbacks(): void {
			$i = 0;
			
			while($i < count($this->bootingCallbacks)) {
				$this->app->call($this->bootingCallbacks[ $i ]);
				
				$i++;
			}
		}
		
		/**
		 * Run the booted callbacks for the provider
		 * @return void
		 */
		public function callBootedCallbacks(): void {
			$i = 0;
			
			while($i < count($this->bootedCallbacks)) {
				$this->app->call($this->bootedCallbacks[ $i ]);
				
				$i++;
			}
		}
	}