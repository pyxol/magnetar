<?php
	declare(strict_types=1);
	
	namespace Magnetar\Queue;
	
	use Magnetar\Queue\Connection;
	use Magnetar\Queue\Channel;
	use Magnetar\Queue\QueueAdapter;
	
	/**
	 * A class that represents an active message queue. It handles the connection,
	 * the channel, and the queue itself. It also provides methods for publishing
	 * messages to the queue and consuming messages from the queue.
	 */
	class Queue {
		/**
		 * Construction method
		 * @param Connection $connection
		 * @param Channel $channel
		 * @param QueueAdapter $queue
		 */
		public function __construct(
			protected Connection $connection,
			protected Channel $channel,
			protected QueueAdapter $adapter
		) {
			
		}
		
		/**
		 * Publish a message to the queue
		 * @param mixed $message Message to publish
		 * @param string $routingKey Routing key for the message
		 * @param array $options Additional options for the message
		 * @return void
		 */
		public function publishMessage(
			mixed $body,
			string $exchange,
			array $options=[]
		): self {
			// send message to queue
			$message = $this->adapter->sendMessage($body, $exchange, $options);
			
			return $this;
		}
		
		/**
		 * Get the connection object
		 * @return Connection The connection object
		 */
		public function getConnection(): Connection {
			return $this->connection;
		}
		
		/**
		 * Get the channel object
		 * @return Channel The channel object
		 */
		public function getChannel(): Channel {
			return $this->channel;
		}
		
		/**
		 * Get the queue adapter object
		 * @return QueueAdapter The queue adapter object
		 */
		public function getAdapter(): QueueAdapter {
			return $this->adapter;
		}
	}