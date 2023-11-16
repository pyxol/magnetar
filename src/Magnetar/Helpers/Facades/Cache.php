<?php
	declare(strict_types=1);
	
	namespace Magnetar\Helpers\Facades;
	
	use Magnetar\Helpers\Facades\Facade;
	
	/**
	 * @method static \Magnetar\Cache\AbstractCacheStore connection(?string $driver_name)
	 * @method static void clear()
	 * @method static bool delete(string $key)
	 * @method static mixed get(string $key, mixed $callback, int $ttl)
	 * @method static array getMany(array $keys)
	 * @method static int|false increment(string $key, int $step)
	 * @method static int|false decrement(string $key, int $step)
	 * @method static bool has(string $key)
	 * @method static array hasMany(array $keys)
	 * @method static mixed set(string $key, mixed $value, int|false $ttl)
	 * @method static void setMany(array $values, int|false $ttl)
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