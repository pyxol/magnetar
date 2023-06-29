<?php
	declare(strict_types=1);
	
	namespace Magnetar\Cache;
	
	use Magnetar\Config;
	use Memcached;
	
	
	
	
	
	
	
	
	// @TODO unfinished?
	
	
	
	
	
	
	
	class MemcachedStore extends AbstractCache {
		protected $memcached;
		protected array $store = [];
		
		public function connect(Config $config): void {
			$this->prefix = $config->get('cache.prefix', '');
			
			$this->memcached = new Memcached();
			$this->memcached->addServer(
				$config->get('cache.memcached.host', 'localhost'),
				$config->get('cache.memcached.port', 11211)
			);
		}
		
		public function clear(): void {
			$this->store = [];
		}
		
		public function delete(string $key): void {
			unset($this->store[ $key ]);
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
			$value = $this->memcached->get($this->prefix . $key);
			
			if($this->memcached->getResultCode() !== Memcached::RES_NOTFOUND) {
				return $value;
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
			$defaultValues = array_fill_keys($keys, null);
			
			$items = $this->memcached->getMulti($keys);
			
			if($this->memcached->getResultCode() === Memcached::RES_NOTFOUND) {
				return $defaultValues;
			}
			
			return array_merge($defaultValues, $items);
		}
		
		/**
		 * Increment the value of an item in the cache.
		 * @param string $key
		 * @param int $value How much to increment by
		 * @return int|false
		 */
		public function increment(string $key, $value=1): int|false {
			return $this->memcached->increment($this->prefix . $key, $value);
		}
		
		/**
		 * Decrement the value of an item in the cache.
		 * @param string $key
		 * @param int $value How much to decrement by
		 * @return int|false
		 */
		public function decrement(string $key, $value=1): int|false {
			return $this->memcached->decrement($this->prefix . $key, $value);
		}
		
		/**
		 * Determine if an item exists in the cache.
		 * @param string $key
		 * @return bool
		 */
		public function has(string $key): bool {
			$this->memcached->get($this->prefix . $key);
			
			return ($this->memcached->getResultCode() !== Memcached::RES_NOTFOUND);
		}
		
		/**
		 * Determine if the given items exist in the cache.
		 * @param array $keys
		 * @return array
		 */
		public function hasMany(array $keys): array {
			$has = [];
			
			foreach($keys as $key) {
				$has[ $key ] = $this->has($key);
			}
			
			return $has;
		}
		
		/**
		 * Store an item in the cache. Returns the value
		 * @param string $key
		 * @param mixed $value
		 * @param int $ttl Number of seconds to store the item. If greater than 30 days, it will be treated as a unix timestamp.
		 * @return mixed
		 */
		public function set(string $key, $value, int $ttl=0): mixed {
			$result = $this->memcached->set(
				$this->prefix . $key,
				$value,
				$ttl
			);
			
			if(false === $result) {
				return false;
			}
			
			return $value;
		}
		
		/**
		 * Store multiple items in the cache
		 * @param array $values
		 * @param int $ttl TTL is not used in this implementation
		 * @return void
		 */
		public function setMany(array $values, int $ttl=0): void {
			foreach($values as $key => $value) {
				$this->set($key, $value, $ttl);
			}
		}
	}