<?php
	declare(strict_types=1);
	
	namespace Magnetar\Cache;
	
	use Magnetar\Config;
	
	class NullStore extends AbstractCacheStore {
		protected function connect(Config $config): void {
			// nothing needed for this method in this class
		}
		
		public function clear(): void {
			// do nothing
		}
		
		public function delete(string $key): bool {
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
			if(is_callable($callback)) {
				return $callback();
			}
			
			return $callback;
		}
		
		/**
		 * Get the values for the given keys. Null is returned for each key that doesn't exist or isn't scalar.
		 * @param array $keys
		 * @return array
		 */
		public function getMany(array $keys): array {
			return array_fill_keys($keys, null);
		}
		
		/**
		 * Set a value in the cache.
		 * @param string $key
		 * @param mixed $value
		 * @param int $ttl Optional. The number of seconds to store the value. If 0, the value will be stored indefinitely.
		 * @return mixed
		 */
		public function set(string $key, mixed $value, int $ttl=0): mixed {
			return $value;
		}
		
		/**
		 * Set multiple values in the cache.
		 * @param array $values
		 * @param int $ttl Optional. The number of seconds to store the value. If 0, the value will be stored indefinitely.
		 * @return void
		 */
		public function setMany(array $values, int $ttl=0): void {
			
		}
	}