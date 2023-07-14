<?php
	declare(strict_types=1);
	
	namespace Magnetar\Helpers\Facades;
	
	/**
	 * 
	 * 
	 * @see Magnetar\Cache\CacheStoreInterface
	 */
	class Log extends Facade {
		/**
		 * Get the named key that this facade represents
		 * @return string
		 */
		protected static function getFacadeKey(): string {
			return 'logger';
		}
	}