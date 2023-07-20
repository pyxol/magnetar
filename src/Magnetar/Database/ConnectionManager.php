<?php
	declare(strict_types=1);
	
	namespace Magnetar\Database;
	
	use Exception;
	
	use Magnetar\Application;
	use Magnetar\Database\AbstractDatabaseAdapter;
	
	class ConnectionManager {
		protected array $connections = [];
		
		protected array $adapters = [
			'mariadb' => MariaDB\DatabaseAdapter::class,
			//'mysql' => MySQL\DatabaseAdapter::class,
			//'pgsql' => PostgreSQL\DatabaseAdapter::class,
			'sqlite' => SQLite\DatabaseAdapter::class,
		];
		
		public function __construct(
			protected Application $app
		) {
			
		}
		
		/**
		 * Returns the active database adapter for the specified driver
		 * @param string|null $connection_name Connection name from the database config file. If no connection name is specified, the default connection is used
		 * @return AbstractDatabaseAdapter
		 * 
		 * @throws Exception
		 */
		public function connection(string|null $connection_name=null): AbstractDatabaseAdapter {
			// interfaces with the app's configuration to create a database connection
			if(null === $connection_name) {
				$connection_name = $this->getDefaultConnectionName() ?? throw new Exception('No default database connection specified');
			}
			
			if(!isset($this->connections[ $connection_name ])) {
				$this->makeConnection($connection_name);
			}
			
			return $this->connections[ $connection_name ];
		}
		
		/**
		 * Creates a new database connection
		 * @param string $connection_name Connection name
		 * @return void
		 * 
		 * @throws Exception
		 */
		protected function makeConnection(string $connection_name): void {
			if(null === ($adapter = $this->getAdapterNameFromConnectionName($connection_name))) {
				throw new Exception('Database driver not specified for connection');
			}
			
			if(null === ($adapter_class = $this->adapters[ $adapter ] ?? null)) {
				throw new Exception('Invalid database driver');
			}
			
			$this->connections[ $connection_name ] = new $adapter_class($connection_name, $this->app) ?? throw new Exception('Invalid database driver');
		}
		
		/**
		 * Get the database adapter from the connection name. Returns null if the adapter cannot be determined from configuration
		 * @param string $connection_name Connection name from the database config file
		 * @return string|null
		 */
		protected function getAdapterNameFromConnectionName(string $connection_name): string|null {
			return $this->app['config']->get('database.connections.'. $connection_name .'.adapter', null);
		}
		
		/**
		 * Returns the default database connection name
		 * @return string|null
		 */
		public function getDefaultConnectionName(): string|null {
			return $this->app['config']->get('database.default', null);
		}
		
		/**
		 * Returns an array of driver names that have been connected to
		 * @return array
		 */
		public function getConnected(): array {
			return array_keys($this->connections);
		}
		
		/**
		 * Returns the database adapter for the specified driver
		 * @param string $connection_name
		 * @return AbstractDatabaseAdapter
		 * 
		 * @throws Exception
		 */
		public function adapter(string $connection_name): string|null {
			return $this->connections[ $connection_name ] ?? throw new Exception('Specified database driver is not connected');
		}
		
		/**
		 * Passes method calls to the default database adapter
		 * @param string $method
		 * @param array $args
		 * @return void
		 */
		public function __call(string $method, array $args) {
			return $this->connection()->$method(...$args);
		}
	}