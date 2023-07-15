<?php
	declare(strict_types=1);
	
	namespace Magnetar\Database;
	
	use Exception;
	
	use Magnetar\Application;
	use Magnetar\Database\AbstractDatabaseAdapter;
	
	class ConnectionManager {
		protected array $connections = [];
		
		protected array $drivers = [
			'mariadb' => MariaDB\DatabaseAdapter::class,
			//'mysql' => MySQL\DatabaseAdapter::class,
			//'pgsql' => PostgreSQL\DatabaseAdapter::class,
			//'sqlite' => SQLite\DatabaseAdapter::class,
		];
		
		public function __construct(
			protected Application $app
		) {
			
		}
		
		/**
		 * Returns the active database adapter for the specified driver
		 * @param string|null $driver_name
		 * @return AbstractDatabaseAdapter
		 * 
		 * @throws Exception
		 */
		public function connection(string|null $driver_name=null): AbstractDatabaseAdapter {
			// interfaces with the app's configuration to create a database connection
			if(null === $driver_name) {
				$driver_name = $this->getDefaultDriver() ?? throw new Exception('No default database driver specified');
			}
			
			if(!isset($this->connections[ $driver_name ])) {
				$this->makeConnection($driver_name);
			}
			
			return $this->connections[ $driver_name ];
		}
		
		/**
		 * Creates a new database connection
		 * @param string $driver_name
		 * @return void
		 * 
		 * @throws Exception
		 */
		protected function makeConnection(string $driver_name): void {
			// match($driver_name) {
			// 	'mariadb' => $this->connections['mariadb'] = new MariaDB\DatabaseAdapter($this->app),
			// 	//'mysql' => $this->connections['mysql'] = new MySQL\DatabaseAdapter($this->app),
			// 	//'pgsql' => $this->connections['pgsql'] = new PostgreSQL\DatabaseAdapter($this->app),
			// 	//'sqlite' => $this->connections['sqlite'] = new SQLite\DatabaseAdapter($this->app),
			// 	default => throw new Exception('Invalid database driver')
			// };
			
			if(null === ($driver_class = $this->drivers[ $driver_name ] ?? null)) {
				throw new Exception('Invalid database driver');
			}
			
			$this->connections[ $driver_name ] = new $driver_class($this->app) ?? throw new Exception('Invalid database driver');
		}
		
		/**
		 * Returns the default database driver
		 * @return string|null
		 */
		public function getDefaultDriver(): string|null {
			return $this->app['config']->get('database.default', null);
		}
		
		/**
		 * Returns an array of driver names that have been connected to
		 * @return array
		 */
		public function getConnectedDrivers(): array {
			return array_keys($this->connections);
		}
		
		/**
		 * Returns the database adapter for the specified driver. If no driver is specified, the default driver is used
		 * @param string $driver_name
		 * @return AbstractDatabaseAdapter
		 * 
		 * @throws Exception
		 */
		public function driver(string $driver_name): string|null {
			return $this->connections[ $driver_name ] ?? throw new Exception('Specified database driver is not connected');
		}
		
		/**
		 * Passes method calls to the active database adapter
		 * @param string $method
		 * @param array $args
		 * @return void
		 */
		public function __call(string $method, array $args) {
			return $this->connection()->$method(...$args);
		}
	}