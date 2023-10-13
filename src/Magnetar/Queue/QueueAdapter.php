<?php
	declare(strict_types=1);
	
	namespace Magnetar\Queue;

use Magnetar\Application;
use RuntimeException;
	
	use Magnetar\Queue\Connection;
	use Magnetar\Queue\Channel;
	use Magnetar\Queue\Message;
	use Magnetar\Queue\Exceptions\QueueAdapterException;
	
	/**
	 * Queue adapter
	 * 
	 * @todo implement setting/managing channel object
	 */
	class QueueAdapter {
		/**
		 * Name of the adapter
		 * @var string
		 */
		const ADAPTER_NAME = '';
		
		/**
		 * Connection object
		 * @var Connection
		 */
		protected Connection $connection;
		
		/**
		 * Channel object
		 * @var array<Channel>
		 */
		protected array $channels = [];
		
		/**
		 * Connection handler
		 * @var mixed
		 */
		protected mixed $qh = null;
		
		/**
		 * Adapter constructor
		 * @param Application $app Application object
		 * @param string $connection_name Name of the connection
		 * @param array $configuration Configuration data to wire up the connection
		 * 
		 * @throws RuntimeException
		 * @throws QueueAdapterException
		 */
		public function __construct(
			protected Application $app,
			protected string $connection_name,
			protected array $connection_config = []
		) {
			// pull the configuration and check if it is valid
			$this->validateRuntime();
			
			// create the connection
			$this->createConnection();
			
			// run any post connection actions
			$this->postConnection();
		}
		
		/**
		 * Validate runtime configuration
		 * @return void
		 * 
		 * @throws RuntimeException
		 * @throws QueueAdapterException
		 */
		protected function validateRuntime(): void {
			// individual adapters are encouraged to override this method
		}
		
		/**
		 * Create the connection to the queue
		 * @return void
		 */
		protected function createConnection(): void {
			// individual adapters should override this method
			throw new QueueAdapterException("Do not use the base QueueAdapter class directly. Use a specific adapter instead.");
		}
		
		/**
		 * Post connection actions (typically character set)
		 * @return void
		 */
		protected function postConnection(): void {
			// individual adapters may override this method
		}
		
		/**
		 * Get the application object
		 * @return Application The application object
		 */
		public function getApp(): Application {
			return $this->app;
		}
		
		/**
		 * Get the adapter name
		 * @return string The name of the adapter
		 */
		public function getAdapterName(): string {
			return self::ADAPTER_NAME;
		}
		
		/**
		 * Get the connection name
		 * @return string The name of the connection
		 */
		public function getConnectionName(): string {
			return $this->connection_name;
		}
		
		/**
		 * Get the connection configuration
		 * @return array The configuration data for the connection
		 */
		public function getConnectionConfig(): array {
			return $this->connection_config;
		}
		
		/**
		 * Get the connection object
		 * @return Connection The connection object
		 */
		public function getConnection(): Connection {
			return $this->connection;
		}
		
		/**
		 * Generate a new Message object
		 * @param mixed $body The raw contents of the message body
		 * @return Message The message object
		 */
		public function makeMessage(mixed $body): Message {
			return new Message($body);
		}
		
		/**
		 * Get a channel object by name
		 * @param string $channelName Name of the channel
		 * @return Channel The channel object
		 * 
		 * @example Queue::channel('my-channel')->publish($message);
		 */
		public function channel(string $channelName): Channel {
			return new Channel($this, $channelName);
		}
		
		/**
		 * Helper method to send a message to the queue
		 * @param string $channel Name of the channel
		 * @param mixed $message The message body
		 * @param string $exchange The exchange to send the message to
		 * @return bool
		 */
		public function publish(
			string $channel,
			mixed $message,
			string $exchange=''
		): bool {
			throw new RuntimeException("Do not call the base QueueAdapter class directly. Use a queue-specific QueueAdapter class that overrides publish().");
		}
	}