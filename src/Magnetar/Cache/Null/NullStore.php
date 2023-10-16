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
		 * {@inheritDoc}
		 */
		protected function wireUp(Config $config): void {
			// nothing needed for this method in this class
		}
		
		/**
		 * {@inheritDoc}
		 */
		public function clear(): void {
			// do nothing
		}
		
		/**
		 * {@inheritDoc}
		 */
		public function delete(string $key): bool {
			return true;
		}
		
		/**
		 * {@inheritDoc}
		 */
		public function get(
			string $key,
			mixed $callback=null,
			int|false $ttl=3600
		): mixed {
			if(is_callable($callback)) {
				return $callback();
			}
			
			return $callback;
		}
		
		/**
		 * {@inheritDoc}
		 */
		public function getMany(array $keys): array {
			return array_fill_keys($keys, null);
		}
		
		/**
		 * {@inheritDoc}
		 */
		public function increment(string $key, int $step=1): int|false {
			return $step;
		}
		
		/**
		 * {@inheritDoc}
		 */
		public function decrement(string $key, int $step=1): int|false {
			return 0;
		}
		
		/**
		 * {@inheritDoc}
		 */
		public function has(string $key): bool {
			return false;
		}
		
		/**
		 * {@inheritDoc}
		 */
		public function hasMany(array $keys): array {
			return array_fill_keys($keys, false);
		}
		
		/**
		 * {@inheritDoc}
		 */
		public function set(
			string $key,
			mixed $value,
			int|false $ttl=3600
		): mixed {
			return $value;
		}
		
		/**
		 * {@inheritDoc}
		 */
		public function setMany(array $values, int|false $ttl=3600): void {
			// do nothing
		}
	}