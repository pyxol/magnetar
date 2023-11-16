<?php
	declare(strict_types=1);
	
	namespace Magnetar\Cache\Memcached;
	
	use Memcached;
	
	use Magnetar\Cache\AbstractCacheStore;
	use Magnetar\Config\Config;
	
	class MemcachedStore extends AbstractCacheStore {
		/**
		 * The Memcached instance
		 * @var Memcached $memcached
		 */
		protected Memcached $memcached;
		
		/**
		 * {@inheritDoc}
		 */
		protected function wireUp(Config $config): void {
			$this->memcached = new Memcached();
			$this->memcached->addServer(
				$config->get('cache.connections.memcached.host', 'localhost'),
				(int)$config->get('cache.connections.memcached.port', 11211)
			);
		}
		
		/**
		 * {@inheritDoc}
		 */
		public function clear(): void {
			if('' === $this->prefix) {
				$this->memcached->flush();
				
				return;
			}
			
			$keys = $this->memcached->getAllKeys();
			
			foreach($keys as $key) {
				if(0 === strpos($key, $this->prefix)) {
					$this->memcached->delete($key);
				}
			}
		}
		
		/**
		 * {@inheritDoc}
		 */
		public function delete(string $key): bool {
			return $this->memcached->delete($this->prefix . $key);
		}
		
		/**
		 * {@inheritDoc}
		 */
		public function get(
			string $key,
			mixed $callback=null,
			int|false $ttl=3600
		): mixed {
			$value = $this->memcached->get($this->prefix . $key);
			
			if($this->memcached->getResultCode() !== Memcached::RES_NOTFOUND) {
				return $value;
			}
			
			if(null === $callback) {
				return null;
			}
			
			if(is_callable($callback)) {
				return $this->set($key, $callback(), $ttl);
			}
			
			return $this->set($key, $callback, $ttl);
		}
		
		/**
		 * {@inheritDoc}
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
		 * {@inheritDoc}
		 */
		public function increment(string $key, int $step=1): int|false {
			return $this->memcached->increment($this->prefix . $key, $step);
		}
		
		/**
		 * {@inheritDoc}
		 */
		public function decrement(string $key, int $step=1): int|false {
			return $this->memcached->decrement($this->prefix . $key, $step);
		}
		
		/**
		 * {@inheritDoc}
		 */
		public function has(string $key): bool {
			$this->memcached->get($this->prefix . $key);
			
			return ($this->memcached->getResultCode() !== Memcached::RES_NOTFOUND);
		}
		
		/**
		 * {@inheritDoc}
		 */
		public function hasMany(array $keys): array {
			$has = [];
			
			foreach($keys as $key) {
				$has[ $key ] = $this->has($key);
			}
			
			return $has;
		}
		
		/**
		 * {@inheritDoc}
		 */
		public function set(
			string $key,
			mixed $value,
			int|false $ttl=3600
		): mixed {
			$result = $this->memcached->set(
				$this->prefix . $key,
				$value,
				((false !== $ttl)?$ttl:0)
			);
			
			if(false === $result) {
				return false;
			}
			
			return $value;
		}
		
		/**
		 * {@inheritDoc}
		 */
		public function setMany(array $values, int|false $ttl=0): void {
			foreach($values as $key => $value) {
				$this->set($key, $value, $ttl);
			}
		}
	}