<?php
	declare(strict_types=1);
	
	namespace Magnetar\Cache;
	
	use Magnetar\Container\Container;
	use Magnetar\Config\Config;
	
	/**
	 * Abstract class for cache stores
	 * @package Magnetar\Cache
	 * @uses Magnetar\Config
	 */
	abstract class AbstractCacheStore implements CacheStoreInterface {
		protected string $prefix = '';
		
		/**
		 * AbstractCacheStore constructor
		 * @param Config $config
		 */
		public function __construct(
			Container $container
		) {
			if('' !== ($this->prefix = $container['config']->get('cache.prefix', ''))) {
				$this->prefix .= ':';
			}
			
			$this->wireUp($container['config']);
		}
		
		// connection
		abstract protected function wireUp(Config $config): void;
	}