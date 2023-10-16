<?php
	declare(strict_types=1);
	
	namespace Magnetar\Cache;
	
	interface CacheStoreInterface {
		/**
		 * Clear entirety of cache. If a prefix is set, only keys with that prefix will be cleared.
		 * @return void
		 */
		public function clear(): void;
		
		/**
		 * Cleanup: delete cache by key
		 * @return bool
		 */
		public function delete(string $key): bool;
		
		/**
		 * Getter: get cache by key
		 * @param string $key The key to get
		 * @param mixed $callback Optional. The value to cache. If callable, this will be called and the value returned is stored in cache. If null, the stored cache value will be returned (defaults to null)
		 * @param int|false $ttl Optional. The time to live in seconds (defaults to 3600). Set to false to not set an expiration time
		 * @return mixed The value of the cache
		 */
		public function get(string $key, mixed $callback=null, int $ttl=3600): mixed;
		
		/**
		 * Getter: get multiple cache by keys
		 * @param array $keys The array of keys to get
		 * @return array The array of values of the cache
		 */
		public function getMany(array $keys): array;
		
		/**
		 * Counter: increment a cache value
		 * @param string $key The key to increment
		 * @param int $step The amount to increment by
		 * @return int|false The new value of the cache, or false on failure
		 */
		public function increment(string $key, int $step=1): int|false;
		
		/**
		 * Counter: decrement a cache value
		 * @param string $key The key to decrement
		 * @param int $step The amount to decrement by
		 * @return int|false The new value of the cache, or false on failure
		 */
		public function decrement(string $key, int $step=1): int|false;
		
		/**
		 * Exister: check if a cache key exists
		 * @param string $key The key to check
		 * @return bool True if the key exists, false otherwise
		 */
		public function has(string $key): bool;
		
		/**
		 * Exister: check if multiple cache keys exist
		 * @param array $keys The array of keys to check
		 * @return array An assoc array of booleans, true if the key exists, false otherwise
		 */
		public function hasMany(array $keys): array;
		
		/**
		 * Setter: set a cache value
		 * @param string $key The key to set
		 * @param mixed $value The value to set
		 * @param int $ttl The time to live in seconds (defaults to 3600). Set to false to not set an expiration time
		 * @return mixed The value of the cache
		 */
		public function set(string $key, mixed $value, int|false $ttl=3600): mixed;
		
		/**
		 * Setter: set multiple cache values
		 * @param array $values The array of key/value pairs to set
		 * @param int $ttl The time to live in seconds (defaults to 3600). Set to false to not set an expiration time
		 * @return void
		 */
		public function setMany(array $values, int|false $ttl=3600): void;
	}