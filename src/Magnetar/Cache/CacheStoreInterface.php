<?php
	declare(strict_types=1);
	
	namespace Magnetar\Cache;
	
	interface CacheStoreInterface {
		// cleanup
		public function clear(): void;
		public function delete(string $key): bool;
		
		// getters
		public function get(string $key): mixed;
		public function getMany(array $keys): array;
		
		// counters
		public function increment(string $key, int $step=1): int|false;
		public function decrement(string $key, int $step=1): int|false;
		
		// existers
		public function has(string $key): bool;
		public function hasMany(array $keys): array;
		
		// setters
		public function set(string $key, $value, int $ttl=0): mixed;
		public function setMany(array $values, int $ttl=0): void;
	}