<?php
	declare(strict_types=1);
	
	namespace Magnetar\Helpers;
	
	class DefaultServiceProviders {
		protected array $providers = [];
		
		/**
		 * Constructor method
		 * @param array|null $providers
		 */
		public function __construct(array|null $providers=null) {
			$this->providers = $providers ?? [
				\Magnetar\Cache\CacheServiceProvider::class,
				\Magnetar\Database\DatabaseServiceProvider::class,
			];
		}
		
		/**
		 * Get an array of default service providers
		 */
		public function toArray(): array {
			return $this->providers;
		}
	}