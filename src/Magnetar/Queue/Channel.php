<?php
	declare(strict_types=1);
	
	namespace Magnetar\Queue;
	
	use RuntimeException;
	
	/**
	 * A class that represents a queue channel
	 * 
	 * @todo implement logging
	 */
	class Channel {
		/**
		 * Channel constructor
		 * @param QueueAdapter $adapter Adapter for this channel
		 * @param string $channelName Name of the channel
		 */
		public function __construct(
			protected QueueAdapter $adapter,
			protected string $channelName
		) {
			// declare queue
			$this->adapter->getConnection()->declareQueue(
				$channelName
			);
		}
		
		/**
		 * Get the name of this channel
		 * @return string Channel name
		 */
		public function getChannelName(): string {
			return $this->channelName;
		}
		
		/**
		 * Send a message to the queue
		 * @param Message $body The message body
		 * @param string $exchange The exchange to send the message to
		 * @return bool Whether the message was sent successfully
		 * 
		 * @throws RuntimeException
		 */
		public function publish(
			Message $message,
			string $exchange=''
		): bool {
			throw new RuntimeException("Do not call the base QueueAdapter class directly. Use a queue-specific QueueAdapter class that overrides publish().");
		}
		
		/**
		 * Get a message from the queue. Callback receives a Message instance as the first parameter.
		 * @param callable|array|string $callback The callback function (or class reference) to execute when a message is received
		 * @return Message|null The message
		 * 
		 * @throws RuntimeException
		 */
		public function consume(callable|array|string $callback): void {
			throw new RuntimeException("Do not call the base QueueAdapter class directly. Use a queue-specific QueueAdapter class that overrides getMessage().");
		}
	}