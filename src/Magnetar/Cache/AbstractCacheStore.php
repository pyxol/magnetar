<?php
	declare(strict_types=1);
	
	namespace Magnetar\Cache;
	
	use Magnetar\Container\Container;
	use Magnetar\Config\Config;
	
	/**
	 * Abstract class for cache stores
	 */
	abstract class AbstractCacheStore implements CacheStoreInterface {
		/**
		 * The prefix to prepend to all cache keys
		 * @var string
		 */
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
		
		/**
		 * Connect to the cache store
		 * @param Config $config The config object
		 * @return void
		 */
		abstract protected function wireUp(Config $config): void;
	}