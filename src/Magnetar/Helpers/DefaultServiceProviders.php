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
				\Magnetar\Filesystem\FilesystemServiceProvider::class,
				\Magnetar\Template\TemplateServiceProvider::class,
			];
		}
		
		/**
		 * Add more service providers to the default list
		 * @param array $providers
		 * @return self
		 */
		public function merge(array $providers): self {
			$this->providers = array_merge($this->providers, $providers);
			
			return new static($this->providers);
		}
		
		/**
		 * Get an array of default service providers
		 */
		public function toArray(): array {
			return $this->providers;
		}
	}