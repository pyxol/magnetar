<?php
	declare(strict_types=1);
	
	namespace Magnetar\Cache;
	
	use Magnetar\Config;
	
	abstract class AbstractCache {
		protected string $prefix = '';
		
		// connection
		abstract public function connect(Config $config): void;
		
		
		
		// cleanup
		abstract public function clear(): void;
		abstract public function delete(string $key): void;
		
		// getters
		abstract public function get(string $key): mixed;
		abstract public function getMany(array $keys): array;
		
		// counters
		abstract public function increment(string $key): int|false;
		abstract public function decrement(string $key): int|false;
		
		// existers
		abstract public function has(string $key): bool;
		abstract public function hasMany(array $keys): array;
		
		// setters
		abstract public function set(string $key, $value, int $ttl=0): mixed;
		abstract public function setMany(array $values, int $ttl=0): void;
	}