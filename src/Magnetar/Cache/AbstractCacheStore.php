<?php
	declare(strict_types=1);
	
	namespace Magnetar\Cache;
	
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
		public function __construct(protected Config $config) {
			if("" !== ($this->prefix = $config->get('cache.prefix', ''))) {
				$this->prefix .= ':';
			}
			
			$this->connect($config);
		}
		
		// connection
		abstract protected function connect(Config $config): void;
	}