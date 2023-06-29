<?php
	declare(strict_types=1);
	
	namespace Magnetar\Config;
	
	use Exception;
	
	class Config {
		protected array $store = [];
		
		public function __construct(string|array|null $values=null) {
			if(is_array($values)) {
				$this->store = $values;
			} elseif(is_string($values)) {
				if(file_exists($values)) {
					try {
						$this->load($values);
					} catch(Exception $e) {
						$this->store = [];
						
						throw new Exception("Could not load config file: {$values}");
					}
				}
			}
		}
		
		/**
		 * Set a config value using optional dot notation
		 * @param string $key The key to set. Dot notation is supported (eg: 'app.name')
		 * @param mixed $value The value to set
		 * @return void
		 */
		public function set(string $key, mixed $value): void {
			if(false === strpos($key, '.')) {
				$this->store[ $key ] = $value;
				
				return;
			}
			
			$tiered_keys = explode('.', $key);
			
			$store = &$this->store;
			
			foreach($tiered_keys as $key) {
				if(!isset($store[ $key ])) {
					$store[ $key ] = [];
				}
				
				$store = &$store[ $key ];
			}
			
			$store = $value;
		}
		
		/**
		 * Get a config value using optional dot notation. Returns $default if not found
		 * @param string $key The key to get. Dot notation is supported (eg: 'app.name')
		 * @param mixed $default The default value to return if the key is not found
		 * @return mixed
		 */
		public function get(string $key, mixed $default=null): mixed {
			// if not dot notation, return the value or default
			if(false === strpos($key, '.')) {
				return $this->store[ $key ] ?? $default;
			}
			
			// dot notation, traverse the array to get the value
			// eg: 'app.name' => $this->store['app']['name']
			$keys = explode('.', $key);
			
			$store = $this->store;
			
			foreach($keys as $key) {
				if(!isset($store[ $key ])) {
					return $default;
				}
				
				$store = $store[ $key ];
			}
			
			return $store;
		}
		
		/**
		 * Check if a config value exists using optional dot notation
		 * @param string $key The key to check. Dot notation is supported (eg: 'app.name')
		 * @return bool
		 */
		public function has(string $key): bool {
			// if not dot notation, return the value or default
			if(false === strpos($key, '.')) {
				return isset($this->store[ $key ]);
			}
			
			// dot notation, traverse the array to get the value
			// eg: 'app.name' => $this->store['app']['name']
			$keys = explode('.', $key);
			
			$store = $this->store;
			
			foreach($keys as $key) {
				if(!isset($store[ $key ])) {
					return false;
				}
				
				$store = $store[ $key ];
			}
			
			return true;
		}
		
		/**
		 * Get all config values
		 * @return array
		 */
		public function all(): array {
			return $this->store;
		}
		
		/**
		 * Set all config values
		 * @param array $values
		 * @return void
		 */
		public function setAll(array $values): void {
			$this->store = $values;
		}
		
		/**
		 * Remove a config value using optional dot notation
		 * @param string $key The key to remove. Dot notation is supported (eg: 'app.name')
		 * @return void
		 */
		public function remove(string $key): void {
			// if not dot notation, return the value or default
			if(false === strpos($key, '.')) {
				unset($this->store[ $key ]);
				
				return;
			}
			
			// dot notation, traverse the array to get the value
			// eg: 'app.name' => $this->store['app']['name']
			$keys = explode('.', $key);
			
			$store = &$this->store;
			
			foreach($keys as $key) {
				if(!isset($store[ $key ])) {
					return;
				}
				
				$store = &$store[ $key ];
			}
			
			unset($store);
		}
		
		/**
		 * Remove all config values
		 * @return void
		 */
		public function removeAll(): void {
			$this->store = [];
		}
		
		/**
		 * Load a config file
		 * @param string $file The file to load
		 * @param string|false $key Optional. The key to set the config values to. Set false to use file for entire config
		 * @return void
		 */
		public function load(string $file, string|false $key=false): void {
			$config_file_path = CONFIGS_DIR . $file . ((false === strpos($file, '.php'))?'.php':'');
			
			// if the file doesn't exist, throw an exception
			if(!file_exists($config_file_path)) {
				throw new Exception('Config file not found: ' . $file);
			}
			
			// load the config file
			$config = require($config_file_path);
			
			// if the config is not an array, throw an exception
			if(!is_array($config)) {
				throw new Exception('Config file must return an array: ' . $file);
			}
			
			// set specific key
			if(false !== $key) {
				$this->set($key, $config);
				
				return;
			}
			
			// set the config values
			$this->setAll($config);
		}
	}