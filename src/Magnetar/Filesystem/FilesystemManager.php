<?php
	declare(strict_types=1);
	
	namespace Magnetar\Filesystem;
	
	use Exception;
	
	use Magnetar\Application;
	use Magnetar\Filesystem\Filesystem;
	
	class FilesystemManager {
		protected array $drives = [];
		
		protected array $adapters = [
			'disk' => Adapter\DiskAdapter::class,
			//'cloud' => Adapter\CloudAdapter::class,
		];
		
		public function __construct(
			protected Application $app
		) {
			
		}
		
		/**
		 * Returns the active filesystem adapter for the specified driver
		 * @param string|null $connection_name Connection name from the filesystem config file. If no connection name is specified, the default connection is used
		 * @return Filesystem
		 * 
		 * @throws Exception
		 */
		public function drive(string|null $name=null): Filesystem {
			// interfaces with the app's configuration to create a filesystem connection
			if(null === $name) {
				$name = $this->getDefaultDriveName() ?? throw new Exception('No default filesystem specified');
			}
			
			if(!isset($this->drives[ $name ])) {
				$this->attachDrive($name);
			}
			
			return $this->drives[ $name ];
		}
		
		/**
		 * Creates a new database connection
		 * @param string $connection_name Connection name
		 * @return void
		 * 
		 * @throws Exception
		 */
		protected function attachDrive(string $name): void {
			if(null === ($adapter = $this->getAdapterNameFromFilesystemName($name))) {
				throw new Exception('Database driver not specified for connection');
			}
			
			if(null === ($adapter_class = $this->adapters[ $adapter ] ?? null)) {
				throw new Exception('Invalid database driver');
			}
			
			$this->drives[ $name ] = new $adapter_class(
				$name,
				$this->app['config']->get('database.connections.'. $name, [])
			) ?? throw new Exception('Invalid database driver');
		}
		
		/**
		 * Get the database adapter from the connection name. Returns null if the adapter cannot be determined from configuration
		 * @param string $name Connection name from the database config file
		 * @return string|null
		 */
		protected function getAdapterNameFromFilesystemName(string $name): string|null {
			return $this->app['config']->get('filesystem.drives.'. $name .'.adapter', null);
		}
		
		/**
		 * Returns the default database connection name
		 * @return string|null
		 */
		public function getDefaultDriveName(): string|null {
			return $this->app['config']->get('filesystem.default', null);
		}
		
		/**
		 * Returns an array of driver names that have been connected to
		 * @return array
		 */
		public function getConnected(): array {
			return array_keys($this->drives);
		}
		
		/**
		 * Returns the database adapter for the specified driver
		 * @param string $name
		 * @return Filesystem
		 * 
		 * @throws Exception
		 */
		public function adapter(string $name): string|null {
			return $this->drives[ $name ] ?? throw new Exception('Specified database driver is not connected');
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