<?php
	declare(strict_types=1);
	
	namespace Magnetar\Auth;
	
	use Exception;
	
	use Magnetar\Application;
	use Magnetar\Auth\AuthenticationAdapter;
	
	/**
	 * Authentication manager
	 * 
	 * @see AuthenticationAdapter
	 */
	class AuthManager {
		/**
		 * Array of authentication adapter instances
		 * @var array<string, AuthenticationAdapter>
		 */
		protected array $connections = [];
		
		/**
		 * Array of authentication adapter classes
		 * @var array
		 */
		protected array $adapters = [
			//'oauth' => OAuth\AuthenticationAdapter::class,
			'session' => Session\AuthenticationAdapter::class,
			'cookie' => Cookie\AuthenticationAdapter::class,
			//'api' => API\AuthenticationAdapter::class,
		];
		
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
		 * Returns the active authentication adapter for the specified driver
		 * @param string|null $connection_name Connection name from the auth config file. If no connection name is specified, the default connection is used
		 * @return AuthenticationAdapter
		 * 
		 * @throws Exception
		 */
		public function connection(string|null $connection_name=null): AuthenticationAdapter {
			// interfaces with the app's configuration to create an authentication connection
			if(null === $connection_name) {
				$connection_name = $this->getDefaultConnectionName() ?? throw new Exception('No default authentication connection specified');
			}
			
			if(!isset($this->connections[ $connection_name ])) {
				$this->makeConnection($connection_name);
			}
			
			return $this->connections[ $connection_name ];
		}
		
		/**
		 * Creates a new authentication connection
		 * @param string $connection_name Connection name
		 * @return void
		 * 
		 * @throws Exception
		 */
		protected function makeConnection(string $connection_name): void {
			if(null === ($adapter_name = $this->getAdapterNameFromConnectionName($connection_name))) {
				throw new Exception('Authentication driver not specified for connection');
			}
			
			if(null === ($adapter_class = $this->adapters[ $adapter_name ] ?? null)) {
				throw new Exception('Invalid authentication driver');
			}
			
			$this->connections[ $connection_name ] = new $adapter_class(
				$connection_name,
				$this->app['config']->get('auth.connections.'. $connection_name, [])
			) ?? throw new Exception('Unable to start authentication driver');
		}
		
		/**
		 * Get the authentication adapter from the connection name. Returns null if the adapter cannot be determined from configuration
		 * @param string $connection_name Connection name from the auth config file
		 * @return string|null
		 */
		protected function getAdapterNameFromConnectionName(string $connection_name): string|null {
			return $this->app['config']->get('auth.connections.'. $connection_name .'.adapter', null);
		}
		
		/**
		 * Returns the default authentication connection name
		 * @return string|null
		 */
		public function getDefaultConnectionName(): string|null {
			return $this->app['config']->get('auth.default', null);
		}
		
		/**
		 * Returns an array of driver names that have been connected to
		 * @return array
		 */
		public function getConnected(): array {
			return array_keys($this->connections);
		}
		
		/**
		 * Returns the authentication adapter for the specified driver
		 * @param string $connection_name
		 * @return AuthenticationAdapter
		 * 
		 * @throws Exception
		 */
		public function adapter(string $connection_name): AuthenticationAdapter {
			return $this->connections[ $connection_name ] ?? throw new Exception('Specified authentication driver is not connected');
		}
		
		/**
		 * Passes method calls to the default authentication adapter
		 * @param string $method
		 * @param array $args
		 * @return mixed
		 * 
		 * @see AuthenticationAdapter
		 */
		public function __call(string $method, array $args): mixed {
			return $this->connection()->$method(...$args);
		}
	}