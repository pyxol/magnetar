<?php
	declare(strict_types=1);
	
	namespace Magnetar\Helpers\Facades;
	
	use Magnetar\Helpers\Facades\Facade;
	
	/**
	 * @method connection(?string $driver_name=null): Magnetar\Cache\AbstractCacheStore
	 * @method clear(): void
	 * @method delete(string $key): bool
	 * @method get(string $key, mixed $callback=null, int $ttl=3600): mixed
	 * @method getMany(array $keys): array
	 * @method increment(string $key, int $step=1): int|false
	 * @method decrement(string $key, int $step=1): int|false
	 * @method has(string $key): bool
	 * @method hasMany(array $keys): array
	 * @method set(string $key, mixed $value, int|false $ttl=3600): mixed
	 * @method setMany(array $values, int|false $ttl=3600): void
	 * 
	 * @see \Magnetar\Cache\StoreManager
	 * @see \Magnetar\Cache\AbstractCacheStore
	 */
	class Cache extends Facade {
		/**
		 * Get the named key that this facade represents
		 * @return string
		 */
		protected static function getFacadeKey(): string {
			return 'cache';
		}
	}