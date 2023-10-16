<?php
	declare(strict_types=1);
	
	namespace Magnetar\Cache\InMemory;
	
	use Magnetar\Cache\AbstractCacheStore;
	use Magnetar\Config\Config;
	
	/**
	 * In-memory cache store. Effectively a singleton cache store that uses an array to store values
	 */
	class InMemoryStore extends AbstractCacheStore {
		/**
		 * Array to store values
		 * @var array
		 */
		protected array $store = [];
		
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
			$this->store = [];
		}
		
		/**
		 * {@inheritDoc}
		 */
		public function delete(string $key): bool {
			unset($this->store[ $key ]);
			
			return true;
		}
		
		/**
		 * {@inheritDoc}
		 */
		public function get(
			string $key,
			mixed $callback=null,
			int $ttl=3600
		): mixed {
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
		 * {@inheritDoc}
		 */
		public function getMany(array $keys): array {
			$values = [];
			
			foreach($keys as $key) {
				$values[ $key ] = $this->get($key);
			}
			
			return $values;
		}
		
		/**
		 * {@inheritDoc}
		 */
		public function increment(string $key, int $step=1): int|false {
			if(!isset($this->store[ $key ])) {
				$this->store[ $key ] = 0;
			}
			
			$this->store[ $key ]++;
			
			return $this->store[ $key ];
		}
		
		/**
		 * {@inheritDoc}
		 */
		public function decrement(string $key, int $step=1): int|false {
			if(!isset($this->store[ $key ])) {
				$this->store[ $key ] = 0;
			}
			
			$this->store[ $key ]--;
			
			return $this->store[ $key ];
		}
		
		/**
		 * {@inheritDoc}
		 */
		public function has(string $key): bool {
			return isset($this->store[ $key ]);
		}
		
		/**
		 * {@inheritDoc}
		 */
		public function hasMany(array $keys): array {
			$has = [];
			
			foreach($keys as $key) {
				$has[ $key ] = isset($this->store[ $key ]);
			}
			
			return $has;
		}
		
		/**
		 * {@inheritDoc}
		 */
		public function set(string $key, mixed $value, int|false $ttl=0): mixed {
			return $this->store[ $key ] = $value;
		}
		
		/**
		 * {@inheritDoc}
		 */
		public function setMany(array $values, int|false $ttl=0): void {
			foreach($values as $key => $value) {
				$this->store[ $key ] = $value;
			}
		}
	}