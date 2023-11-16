<?php
	declare(strict_types=1);
	
	namespace Magnetar\Cache;
	
	use Exception;
	
	use Magnetar\Application;
	use Magnetar\Cache\AbstractCacheStore;
	
	/**
	 * Manages cache stores
	 */
	class StoreManager {
		/**
		 * Array of cache connections
		 * @var array
		 */
		protected array $connections = [];
		
		/**
		 * Constructor
		 */
		public function __construct(
			/**
			 * The application instance
			 * @var Application
			 */
			protected Application $app
		) {
			
		}
		
		/**
		 * Returns the active cache store for the specified driver
		 * @param string|null $driver_name
		 * @return AbstractCacheStore
		 * 
		 * @throws Exception
		 */
		public function connection(string|null $driver_name=null): AbstractCacheStore {
			// interfaces with the app's configuration to create the default cache store
			// unless overwritten by driver_name
			
			if(null === $driver_name) {
				$driver_name = $this->app->make('config')->get('cache.default', null);
				
				if(null === $driver_name) {
					throw new Exception('No default cache driver specified');
				}
			}
			
			if(!isset($this->connections[ $driver_name ])) {
				$this->makeConnection($driver_name);
			}
			
			return $this->connections[ $driver_name ];
		}
		
		/**
		 * Creates a new cache store instance
		 * @param string $driver_name
		 * @return void
		 * 
		 * @throws Exception
		 */
		protected function makeConnection(string $driver_name): void {
			match($driver_name) {
				'memcached' => $this->connections['memcached'] = new Memcached\MemcachedStore($this->app),
				'inmemory' => $this->connections['inmemory'] = new InMemory\InMemoryStore($this->app),
				'null' => $this->connections['null'] = new Null\NullStore($this->app),
				default => throw new Exception('Invalid cache driver ('. $driver_name .')')
			};
		}
		
		/**
		 * Passes method calls to the active cache store
		 * @param string $method
		 * @param array $args
		 * @return mixed
		 * 
		 * @see \Magnetar\Cache\AbstractCacheStore
		 */
		public function __call(string $method, array $args): mixed {
			return $this->connection()->$method(...$args);
		}
	}