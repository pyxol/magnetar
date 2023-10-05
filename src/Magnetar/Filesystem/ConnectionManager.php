<?php
	declare(strict_types=1);
	
	namespace Magnetar\Filesystem;
	
	use Exception;
	use RuntimeException;
	
	use Magnetar\Application;
	use Magnetar\Filesystem\DiskAdapter;
	
	/**
	 * Filesystem manager that handles filesystem adapter connections
	 */
	class ConnectionManager {
		/**
		 * Array of filesystem connections
		 * @var array
		 */
		protected array $drives = [];
		
		/**
		 * Array of filesystem adapters
		 * @var array
		 */
		protected array $adapters = [
			'disk' => Disk\DiskAdapter::class,
			's3' => S3\DiskAdapter::class,
		];
		
		public function __construct(
			/**
			 * The application instance
			 * @var Application
			 */
			protected Application $app
		) {
			
		}
		
		/**
		 * Returns the active disk adapter for the specified drive
		 * @param string|null|null $connection_name Connection name from the disk config file
		 * @return DiskAdapter
		 * 
		 * @throws Exception
		 */
		public function connection(string|null $connection_name=null): DiskAdapter {
			// interfaces with the app's configuration to create a drive connection
			if(null === $connection_name) {
				$connection_name = $this->getDefaultDriveName() ?? throw new Exception('No default disk drive specified');
			}
			
			if(!isset($this->drives[ $connection_name ])) {
				$this->makeConnection($connection_name);
			}
			
			return $this->drives[ $connection_name ] ?? $this->makeConnection($connection_name);
		}
		
		/**
		 * Creates a new disk drive connection
		 * @param string $connection_name Connection name
		 * @return DiskAdapter
		 * 
		 * @throws Exception
		 */
		protected function makeConnection(string $connection_name): DiskAdapter {
			if(null === ($adapter_name = $this->getAdapterNameFromDriveName($connection_name))) {
				throw new Exception('Disk drive adapter not specified for connection');
			}
			
			if(null === ($adapter_class = $this->adapters[ $adapter_name ] ?? null)) {
				throw new Exception('Unknown disk drive adapter');
			}
			
			return $this->drives[ $connection_name ] = new $adapter_class(
				$connection_name,
				$this->app['config']->get('files.drives.'. $connection_name, [])
			) ?? throw new Exception('Failed to start disk drive');
		}
		
		/**
		 * Get the disk adapter from the connection name. Returns null if the adapter cannot be determined from configuration
		 * @param string $name Connection name from the disk config file
		 * @return string|null The disk adapter name
		 */
		protected function getAdapterNameFromDriveName(string $name): string|null {
			return $this->app['config']->get('files.drives.'. $name .'.adapter', null);
		}
		
		/**
		 * Returns the default disk connection name
		 * @return string|null The default disk connection name
		 */
		public function getDefaultDriveName(): string|null {
			return $this->app['config']->get('files.default', null);
		}
		
		/**
		 * Returns an array of driver names that have been connected to
		 * @return array An array of driver names
		 */
		public function getConnected(): array {
			return array_keys($this->drives);
		}
		
		/**
		 * Returns the disk adapter for the specified driver
		 * @param string $name Connection name from the disk config file
		 * @return DiskAdapter The disk adapter
		 * 
		 * @throws RuntimeException
		 */
		public function adapter(string $name): DiskAdapter {
			return $this->drives[ $name ] ?? throw new RuntimeException('Specified disk drive is not configured');
		}
		
		/**
		 * Alias for adapter(). Returns the disk adapter for the specified disk name
		 * @param string $name Connection name from the files config file
		 * @return DiskAdapter
		 * 
		 * @throws RuntimeException
		 */
		public function drive(string $name): DiskAdapter {
			return $this->adapter($name);
		}
		
		/**
		 * Passes method calls to the default disk adapter
		 * @param string $method The method name
		 * @param array $args The method arguments
		 * @return mixed
		 * 
		 * @see DiskAdapter
		 */
		public function __call(string $method, array $args): mixed {
			return $this->connection()->$method(...$args);
		}
	}