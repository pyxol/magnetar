<?php
	declare(strict_types=1);
	
	namespace Magnetar\Cache\Null;
	
	use Magnetar\Cache\AbstractCacheStore;
	use Magnetar\Config\Config;
	
	/**
	 * A Null-based cache store that effectively does nothing and always returns null
	 */
	class NullStore extends AbstractCacheStore {
		/**
		 * Unused connect method
		 * @param Config $config Unused
		 * @return void
		 */
		protected function wireUp(Config $config): void {
			// nothing needed for this method in this class
		}
		
		/**
		 * Clear entirety of cache (does nothing)
		 * @return void
		 */
		public function clear(): void {
			// do nothing
		}
		
		/**
		 * Delete a value from the cache (always returns true)
		 * @param string $key Unused
		 * @return bool
		 */
		public function delete(string $key): bool {
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
			if(is_callable($callback)) {
				return $callback();
			}
			
			return $callback;
		}
		
		/**
		 * Get the values for the given keys. Null is returned for each key that doesn't exist or isn't scalar
		 * @param array $keys
		 * @return array
		 */
		public function getMany(array $keys): array {
			return array_fill_keys($keys, null);
		}
		
		/**
		 * Increment the value of an item in the cache. This method returns the $step passed to it
		 * @param string $key
		 * @param int $step
		 * @return int|false
		 */
		public function increment(string $key, int $step=1): int|false {
			return $step;
		}
		
		/**
		 * Decrement the value of an item in the cache. This method always returns 0
		 * @param string $key Unused
		 * @param int $step Unused
		 * @return int|false Always returns 0
		 */
		public function decrement(string $key, int $step=1): int|false {
			return 0;
		}
		
		/**
		 * Determine if an item exists in the cache. This method always returns false
		 * @param string $key The key to check
		 * @return bool Always returns false
		 */
		public function has(string $key): bool {
			return false;
		}
		
		/**
		 * Determine if the given items exist in the cache. This method always returns false for each key
		 * @param array $keys The keys to check
		 * @return array An assoc array of booleans, true if the key exists, false otherwise
		 */
		public function hasMany(array $keys): array {
			return array_fill_keys($keys, false);
		}
		
		/**
		 * Store an item in the cache. Returns the value
		 * @param string $key The key to store
		 * @param mixed $value The value to store
		 * @param int $ttl Optional. The number of seconds to store the value. If 0, the value will be stored indefinitely
		 * @return mixed The value of the cache
		 */
		public function set(string $key, mixed $value, int $ttl=0): mixed {
			return $value;
		}
		
		/**
		 * Store multiple items in the cache
		 * @param array $values An assoc array of key => value
		 * @param int $ttl Optional. The number of seconds to store the value. If 0, the value will be stored indefinitely
		 * @return void
		 */
		public function setMany(array $values, int $ttl=0): void {
			// do nothing
		}
	}