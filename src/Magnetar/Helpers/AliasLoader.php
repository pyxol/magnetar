<?php
	declare(strict_types=1);
	
	namespace Magnetar\Helpers;
	
	class AliasLoader {
		/**
		 * Indicates if a loader has been registered
		 * @var bool
		 */
		protected $loaded = false;
		
		
		protected static $aliasNamespace = 'Facades\\';
		
		/**
		 * Singleton instance
		 * @var AliasLoader
		 */
		protected static $instance;
		
		/**
		 * Set the aliases to be loaded
		 * @param array $aliases
		 */
		public function __construct(
			/**
			 * The aliases to be loaded
			 * @var array
			 */
			protected array $aliases=[]
		) {
			
		}
		
		/**
		 * Get the singleton instance of AliasLoader
		 * @param array $aliases
		 * @return AliasLoader
		 */
		public static function getInstance(array $aliases=[]): AliasLoader {
			// if the instance hasn't been created yet, use the given aliases and create it
			if(is_null(static::$instance)) {
				return static::$instance = new static($aliases);
			}
			
			$aliases = array_merge(static::$instance->getAliases(), $aliases);
			
			static::$instance->setAliases($aliases);
			
			return static::$instance;
		}
		
		/**
		 * Load a known class alias if available
		 * @param string $alias
		 * @return bool|null
		 */
		public function load(string $alias): bool|null {
			if(static::$aliasNamespace && str_starts_with($alias, static::$aliasNamespace)) {
				$this->loadFacade($alias);
				
				return true;
			}
			
			if(isset($this->aliases[ $alias ])) {
				return class_alias($this->aliases[ $alias ], $alias);
			}
			
			return null;
		}
		
		/**
		 * Load a facade class
		 * @param string $alias
		 * @return void
		 */
		protected function loadFacade(string $alias): void {
			$facade = str_replace(static::$aliasNamespace, '', $alias);
			$facade = str_replace('\\', '', $facade);
			$facade = str_replace('_', '', $facade);
			$facade = strtolower($facade);
			$facade = ucfirst($facade);
			
			$facade = static::$aliasNamespace . $facade;
			
			$this->load($facade);
		}
		
		/**
		 * Add an alias to the loader
		 * @param string $class
		 * @param string $alias
		 * @return void
		 */
		public function alias(string $class, string $alias): void {
			$this->aliases[ $class ] = $alias;
		}
		
		/**
		 * Register the loader on the auto-loader stack
		 * @return void
		 */
		public function register(): void {
			if(!$this->loaded) {
				$this->prependToLoaderStack();
				
				$this->loaded = true;
			}
		}
		
		/**
		 * Prepend the load method to the auto-loader stack
		 * @return void
		 */
		protected function prependToLoaderStack(): void {
			spl_autoload_register([$this, 'load'], true, true);
		}
		
		/**
		 * Get the registered aliases
		 * @return array
		 */
		public function getAliases(): array {
			return $this->aliases;
		}
		
		/**
		 * Set the registered aliases
		 * @param array $aliases
		 * @return void
		 */
		public function setAliases(array $aliases): void {
			$this->aliases = $aliases;
		}
		
		/**
		 * Indicates if the loader has been registered
		 * @return bool
		 */
		public function isLoaded(): bool {
			return $this->loaded;
		}
		
		/**
		 * Set the "loaded" state of the loader
		 * @param bool $value
		 * @return void
		 */
		public function setLoaded(bool $value): void {
			$this->loaded = $value;
		}
		
		/**
		 * Set the value of the singleton AliasLoader
		 * @param AliasLoader $loader
		 * @return void
		 */
		public static function setInstance(AliasLoader $loader): void {
			static::$instance = $loader;
		}
		
		/**
		 * Clone method
		 * @return void
		 */
		private function __clone(): void {
			
		}
	}