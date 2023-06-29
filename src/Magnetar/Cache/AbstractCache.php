<?php
	declare(strict_types=1);
	
	namespace Magnetar\Cache;
	
	use Magnetar\Config;
	
	abstract class AbstractCache {
		protected string $prefix = '';
		
		public function __construct(protected Config $config) {
			if("" !== ($this->prefix = $config->get('cache.prefix', ''))) {
				$this->prefix .= ':';
			}
			
			$this->connect($config);
		}
		
		// connection
		abstract protected function connect(Config $config): void;
		
		// cleanup
		abstract public function clear(): void;
		abstract public function delete(string $key): bool;
		
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