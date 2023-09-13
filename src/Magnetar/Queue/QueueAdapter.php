<?php
	declare(strict_types=1);
	
	namespace Magnetar\Queue;
	
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
		 * @var Channel
		 */
		protected Channel $channel;
		
		/**
		 * Connection handler
		 * @var mixed
		 */
		protected mixed $qh = null;
		
		/**
		 * Adapter constructor
		 * @param string $connection_name Name of the connection
		 * @param array $configuration Configuration data to wire up the connection
		 * 
		 * @throws RuntimeException
		 * @throws QueueAdapterException
		 */
		public function __construct(
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
		 * return void
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
		 * @param mixed $body The message body
		 * @return Message
		 */
		public function makeMessage(mixed $message): Message {
			if(is_object($message)) {
				return new Message($this->channel, $message->body);
			}
			
			return new Message($this->channel, $message);
		}
		
		/**
		 * Parse a message from the queue
		 * @param mixed $message The message to parse
		 * @return Message The parsed message
		 */
		public function parseMessage(mixed $message): Message {
			return $this->makeMessage($message);
		}
		
		/**
		 * Creates a new Message object
		 * @param mixed $message The body of the message to create
		 * @return Message
		 */
		public function createMessage(mixed $message): Message {
			return $this->parseMessage($message);
		}
		
		/**
		 * Send a message to the queue
		 * @param Channel $channel The channel to send the message to
		 * @param Message $body The message body
		 * @param string $exchange The exchange to send the message to
		 * @return bool Whether the message was sent successfully
		 * 
		 * @throws RuntimeException
		 */
		public function sendMessage(
			Channel $channel,
			Message $message,
			string $exchange=''
		): bool {
			throw new RuntimeException("Do not call the base QueueAdapter class directly. Use a queue-specific QueueAdapter class that overrides sendMessage().");
		}
		
		
		
	}