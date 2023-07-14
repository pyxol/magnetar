<?php
	declare(strict_types=1);
	
	namespace Magnetar\Database;
	
	use Exception;
	
	use Magnetar\Application;
	use Magnetar\Database\AbstractDatabaseAdapter;
	
	class ConnectionManager {
		protected array $connections = [];
		
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
			// interfaces with the app's configuration to create the default database connection
			// unless overwritten by driver_name
			
			if(is_null($driver_name)) {
				$driver_name = $this->app->make('config')->get('database.default', null);
				
				if(is_null($driver_name)) {
					throw new Exception("No default database driver specified");
				}
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
			match($driver_name) {
				'mariadb' => $this->connections['mariadb'] = new MariaDB\DatabaseAdapter($this->app),   // ->getInstance() ?
				//'mysql' => $this->connections['mysql'] = new MySQL\DatabaseAdapter($this->app),   // ->getInstance() ?
				//'pgsql' => $this->connections['pgsql'] = new PostgreSQL\DatabaseAdapter($this->app),   // ->getInstance() ?
				//'sqlite' => $this->connections['sqlite'] = new SQLite\DatabaseAdapter($this->app),   // ->getInstance() ?
				default => throw new Exception('Invalid database driver')
			};
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