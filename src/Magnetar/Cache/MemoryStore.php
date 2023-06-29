<?php
	declare(strict_types=1);
	
	namespace Magnetar\Cache;
	
	use Magnetar\Config;
	
	class MemoryStore extends AbstractCacheStore {
		protected array $store = [];
		
		protected function connect(Config $config): void {
			// nothing needed for this method in this class
		}
		
		public function clear(): void {
			$this->store = [];
		}
		
		public function delete(string $key): bool {
			unset($this->store[ $key ]);
			
			return true;
		}
		
		/**
		 * Get a value from the cache. If the value does not exist, null is returned.
		 * If a callback is provided, it will be called and the return value will be
		 * stored in the cache and returned.
		 *
		 * @param string $key
		 * @param mixed $callback Optional. The value to store in cache if the key does not exist. If callable, the return value will be stored.
		 * @return mixed
		 */
		public function get(string $key, mixed $callback=null): mixed {
			if(isset($this->store[ $this->prefix . $key ])) {
				return $this->store[ $this->prefix . $key ];
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
		 * Get the values for the given keys. Null is returned for each key that doesn't exist or isn't scalar.
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
		 * Increment the value of an item in the cache.
		 * @param string $key
		 * @return int|bool
		 */
		public function increment(string $key): int|false {
			if(!isset($this->store[ $this->prefix . $key ])) {
				$this->store[ $this->prefix . $key ] = 0;
			}
			
			$this->store[ $this->prefix . $key ]++;
			
			return $this->store[ $this->prefix . $key ];
		}
		
		/**
		 * Decrement the value of an item in the cache.
		 * @param string $key
		 * @return int|bool
		 */
		public function decrement(string $key): int|false {
			if(!isset($this->store[ $this->prefix . $key ])) {
				$this->store[ $this->prefix . $key ] = 0;
			}
			
			$this->store[ $this->prefix . $key ]--;
			
			return $this->store[ $this->prefix . $key ];
		}
		
		/**
		 * Determine if an item exists in the cache.
		 * @param string $key
		 * @return bool
		 */
		public function has(string $key): bool {
			return isset($this->store[ $this->prefix . $key ]);
		}
		
		/**
		 * Determine if the given items exist in the cache.
		 * @param array $keys
		 * @return array
		 */
		public function hasMany(array $keys): array {
			$has = [];
			
			foreach($keys as $key) {
				$has[ $key ] = isset($this->store[ $this->prefix . $key ]);
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
			return $this->store[ $this->prefix . $key ] = $value;
		}
		
		/**
		 * Store multiple items in the cache
		 * @param array $values
		 * @param int $ttl TTL is not used in this implementation
		 * @return void
		 */
		public function setMany(array $values, int $ttl=0): void {
			foreach($values as $key => $value) {
				$this->store[ $this->prefix . $key ] = $value;
			}
		}
	}