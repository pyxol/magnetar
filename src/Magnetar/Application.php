<?php
	declare(strict_types=1);
	
	namespace Magnetar;
	
	use Magnetar\Container\Container;
	
	class Application extends Container {
		protected string|null $basePath = null;
		
		protected bool $bootstrapped = false;
		protected bool $booted = false;
		
		protected static array $configs = [];
		
		/**
		 * Application constructor
		 * @param string|null $base_path The full directory path of the application including trailing string
		 * @return void
		 */
		public function __construct(string|null $base_path=null) {
			if($base_path) {
				$this->setBasePath($base_path);
			}
			
			$this->registerBaseBindings();
			$this->registerCoreContainerAliases();
		}
		
		/**
		 * Set the base path of the application
		 * @param string $base_path
		 * @return void
		 */
		public function setBasePath(string $base_path): void {
			$this->base_path = realpath(rtrim($base_path, DIRECTORY_SEPARATOR)) . DIRECTORY_SEPARATOR;
		}
		
		/**
		 * Get the base path of the application
		 * @return string
		 */
		public function basePath(string $rel_path=''): string {
			return $this->base_path . ltrim($rel_path, DIRECTORY_SEPARATOR);
		}
		
		/**
		 * Has the application been bootstrapped?
		 * @return bool
		 */
		public function hasBeenBootstrapped(): bool {
			return $this->bootstrapped;
		}
		
		/**
		 * Run the given array of bootstrap classes
		 *
		 * @param string[] $bootstrappers
		 * @return void
		 */
		public function bootstrapWith(array $bootstrappers): void {
			$this->bootstrapped = true;
			
			foreach($bootstrappers as $bootstrapper) {
				$this->make($bootstrapper)->bootstrap($this);
			}
		}
		
		/**
		 * Register the basic bindings into the container.
		 *
		 * @return void
		 */
		protected function registerBaseBindings() {
			static::setInstance($this);
			
			$this->instance('app', $this);
			$this->instance(Container::class, $this);
		}
		
		/**
		 * Register a core list of container aliases
		 * @return void
		 */
		public function registerCoreContainerAliases(): void {
			foreach([
				'config' => [ \Magnetar\Config\Config::class, \Magnetar\Config\Config::class ],
				'database' => [ \Magnetar\Database\Database::class, \Magnetar\Database\Database::class ],
			] as $key => $aliases) {
				foreach($aliases as $alias) {
					$this->alias($key, $alias);
				}
			}
		}
	}