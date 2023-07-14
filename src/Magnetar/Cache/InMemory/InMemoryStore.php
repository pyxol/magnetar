<?php
	declare(strict_types=1);
	
	namespace Magnetar\Cache\InMemory;
	
	use Magnetar\Cache\AbstractCacheStore;
	use Magnetar\Config\Config;
	
	class InMemoryStore extends AbstractCacheStore {
		protected array $store = [];
		
		/**
		 * Unused connect method
		 * @param Config $config
		 * @return void
		 */
		protected function wireUp(Config $config): void {
			// nothing needed for this method in this class
		}
		
		/**
		 * Clear entirety of cache
		 * @return void
		 */
		public function clear(): void {
			$this->store = [];
		}
		
		/**
		 * Delete a value from the cache
		 * @param string $key
		 * @return bool
		 */
		public function delete(string $key): bool {
			unset($this->store[ $key ]);
			
			return true;
		}
		
		/**
		 * Get a value from the cache. If the value does not exist, null is returned.
		 * If a callback is provided, it will be called and the return value will be
		 * stored in the cache and returned
		 *
		 * @param string $key
		 * @param mixed $callback Optional. The value to store in cache if the key does not exist. If callable, the return value will be stored
		 * @return mixed
		 */
		public function get(string $key, mixed $callback=null): mixed {
			if(isset($this->store[ $key ])) {
				return $this->store[ $key ];
			}
			
			if(is_null($callback)) {
				return null;
			}
			
			if(is_callable($callback)) {
				return $this->set($key, $callback());
			}
			
			return $this->set($key, $callback);
		}
		
		/**
		 * Get the values for the given keys. Null is returned for each key that doesn't exist or isn't scalar
		 * @param array $keys
		 * @return array
		 */
		public function getMany(array $keys): array {
			$values = [];
			
			foreach($keys as $key) {
				$values[ $key ] = $this->get($key);
			}
			
			return $values;
		}
		
		/**
		 * Increment the value of an item in the cache
		 * @param string $key
		 * @param int $step How much to increment by
		 * @return int|bool
		 */
		public function increment(string $key, int $step=1): int|false {
			if(!isset($this->store[ $key ])) {
				$this->store[ $key ] = 0;
			}
			
			$this->store[ $key ]++;
			
			return $this->store[ $key ];
		}
		
		/**
		 * Decrement the value of an item in the cache
		 * @param string $key
		 * @param int $step How much to decrement by
		 * @return int|bool
		 */
		public function decrement(string $key, int $step=1): int|false {
			if(!isset($this->store[ $key ])) {
				$this->store[ $key ] = 0;
			}
			
			$this->store[ $key ]--;
			
			return $this->store[ $key ];
		}
		
		/**
		 * Determine if an item exists in the cache
		 * @param string $key
		 * @return bool
		 */
		public function has(string $key): bool {
			return isset($this->store[ $key ]);
		}
		
		/**
		 * Determine if the given items exist in the cache
		 * @param array $keys
		 * @return array
		 */
		public function hasMany(array $keys): array {
			$has = [];
			
			foreach($keys as $key) {
				$has[ $key ] = isset($this->store[ $key ]);
			}
			
			return $has;
		}
		
		/**
		 * Store an item in the cache. Returns the value
		 * @param string $key
		 * @param mixed $value
		 * @param int $ttl TTL is not used in this implementation
		 * @return mixed
		 */
		public function set(string $key, $value, int $ttl=0): mixed {
			return $this->store[ $key ] = $value;
		}
		
		/**
		 * Store multiple items in the cache
		 * @param array $values
		 * @param int $ttl TTL is not used in this implementation
		 * @return void
		 */
		public function setMany(array $values, int $ttl=0): void {
			foreach($values as $key => $value) {
				$this->store[ $key ] = $value;
			}
		}
	}