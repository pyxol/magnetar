<?php
	declare(strict_types=1);
	
	namespace Magnetar\Helpers\Facades;
	
	use Magnetar\Helpers\Facades\Facade;
	
	/**
	 * @method connection(?string $driver_name=null): Magnetar\Cache\AbstractCacheStore;
	 * 
	 * @see Magnetar\Cache\StoreManager
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