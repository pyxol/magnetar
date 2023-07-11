<?php
	declare(strict_types=1);
	
	namespace Magnetar\Database;
	
	use Exception;
	
	use Magnetar\Application;
	use Magnetar\Database\DatabaseAdapterInterface;
	
	class ConnectionManager {
		protected array $connections = [];
		
		public function __construct(
			protected Application $app
		) {
			
		}
		
		/**
		 * Returns the active database adapter for the specified driver
		 * @param string|null $driver_name
		 * @return DatabaseAdapterInterface
		 * 
		 * @throws Exception
		 */
		public function connection(string|null $driver_name=null): DatabaseAdapterInterface {
			// interfaces with the app's configuration to create the default database connection
			// unless overwritten by driver_name
			
			
			print "<p>@TODO: Somehow app in ConnectionManager::connection() is effectively empty</p>\n";
			print "<p>Likely an issue with ConnectionManager being called far too early, maybe different class should be called in Application-&gt;registerCoreContainerAliases()</p>";
			print "ConnectionManager::connection - ". $this->app['config']->get('app.timezone') ."<br>\n";
			
			print self::class .".connection(): Somehow need to pass an instance of the app to this class<br>\n";
			
			die("<pre>". esc_html(print_r($this->app['config'], true)));
			
			//// this->app doesn't seem to be updated, even though it's passed by reference
			//print "<pre>config.get=". print_r([
			//	//'this.app' => $this->app,
			//	//'this->app->make(config)' => $this->app->make('config'),
			//	//'this.app.make(config)->all' => $this->app->make('config')->all(),
			//	//'this.app->config->all' => $this->app->config->all(),
			//	//'this.app[config]->all' => $this->app['config']->all(),
			//], true) ."</pre><br>\n";
			//die;
			
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
		 */
		protected function makeConnection(string $driver_name): void {
			match($driver_name) {
				'mariadb' => $this->connections['mariadb'] = new MariaDB\Database($app),
				'mysql' => $this->connections['mysql'] = new MySQL\Database($app),
				'pgsql' => $this->connections['pgsql'] = new PostgreSQL\Database($app),
				'sqlite' => $this->connections['sqlite'] = new SQLite\Database($app),
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