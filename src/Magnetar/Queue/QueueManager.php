<?php
	declare(strict_types=1);
	
	namespace Magnetar\Queue;
	
	use Exception;
	
	use Magnetar\Application;
	use Magnetar\Queue\QueueAdapter;
	
	/**
	 * Queue connection manager
	 */
	class QueueManager {
		/**
		 * Array of queue connection instances
		 * @var array<string, QueueAdapter>
		 */
		protected array $connections = [];
		
		/**
		 * Array of queue adapter classes
		 * @var array<string, string>
		 */
		protected array $available_adapters = [
			'redis' => Redis\QueueAdapter::class,
			'rabbitmq' => RabbitMQ\QueueAdapter::class,
		];
		
		/**
		 * QueueManager constructor
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
		 * Returns the active queue adapter for the specified driver
		 * @param string|null $connection_name Connection name from the queue config file. If no connection name is specified, the default connection is used
		 * @return QueueAdapter
		 * 
		 * @throws Exception
		 */
		public function connection(string|null $connection_name=null): QueueAdapter {
			// interfaces with the app's configuration to create a queue connection
			if(null === $connection_name) {
				$connection_name = $this->getDefaultConnectionName() ?? throw new Exception('No default queue connection specified');
			}
			
			if(!isset($this->connections[ $connection_name ])) {
				$this->makeConnection($connection_name);
			}
			
			return $this->connections[ $connection_name ];
		}
		
		/**
		 * Creates a new queue connection
		 * @param string $connection_name Connection name
		 * @return void
		 * 
		 * @throws Exception
		 */
		protected function makeConnection(string $connection_name): void {
			if(null === ($adapter = $this->getAdapterNameFromConnectionName($connection_name))) {
				throw new Exception('Queue driver not specified for connection');
			}
			
			if(null === ($adapter_class = $this->available_adapters[ $adapter ] ?? null)) {
				throw new Exception('Invalid queue driver');
			}
			
			$this->connections[ $connection_name ] = new $adapter_class(
				$this->app,
				$connection_name,
				$this->app['config']->get('queue.connections.'. $connection_name, [])
			) ?? throw new Exception('Unable to start queue driver');
		}
		
		/**
		 * Get the queue adapter from the connection name. Returns null if the adapter cannot be determined from configuration
		 * @param string $connection_name Connection name from the queue config file
		 * @return string|null
		 */
		protected function getAdapterNameFromConnectionName(string $connection_name): string|null {
			return $this->app['config']->get('queue.connections.'. $connection_name .'.adapter', null);
		}
		
		/**
		 * Returns the default queue connection name
		 * @return string|null
		 */
		public function getDefaultConnectionName(): string|null {
			return $this->app['config']->get('queue.default', null);
		}
		
		/**
		 * Returns an array of driver names that have been connected to
		 * @return array
		 */
		public function getConnected(): array {
			return array_keys($this->connections);
		}
		
		/**
		 * Returns the queue adapter for the specified driver
		 * @param string $connection_name
		 * @return QueueAdapter
		 * 
		 * @throws Exception
		 */
		public function adapter(string $connection_name): QueueAdapter {
			return $this->connections[ $connection_name ] ?? throw new Exception('Specified queue driver is not connected');
		}
		
		/**
		 * Passes method calls to the default queue adapter
		 * @param string $method
		 * @param array $args
		 * @return mixed
		 * 
		 * @see \Magnetar\Queue\QueueAdapter
		 */
		public function __call(string $method, array $args): mixed {
			return $this->connection()->$method(...$args);
		}
	}