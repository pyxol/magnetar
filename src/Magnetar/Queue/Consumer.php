<?php
	declare(strict_types=1);
	
	namespace Magnetar\Queue;
	
	use Exception;
	
	use Magnetar\Queue\Queue;
	
	/**
	 * A class that represents a queue consumer. Accepts messages from
	 * the queue and processes them.
	 */
	class Consumer {
		/**
		 * Construction method
		 * @param Queue $connection The queue this consumer belongs to
		 * @param Channel $channel The channel this consumer belongs to
		 * @param string $consumer_tag The consumer tag for this consumer
		 */
		public function __construct(
			protected Queue $queue,
			protected Channel $channel,
			protected string $consumer_tag
		) {
			
		}
		
		/**
		 * The main loop for a consumer. Waits for messages from the queue.
		 * To run the loop, all that is needed is $consumer->waitForMessage();
		 * @return void
		 */
		public function waitForMessage(): void {
			while($this->channel->is_open()) {
				$this->channel->wait();
			}
		}
		
		/**
		 * Handle a message from this queue
		 * @param mixed $message
		 * @return void
		 */
		protected function handle(mixed $message): void {
			$this->process(
				$this->queue->getAdapter()->parseMessage($message)
			);
		}
		
		/**
		 * Provides the exchange name for this class
		 * @return string
		 */
		public function getExchangeName(): string {
			throw new Exception("Do not call the base Consumer class directly. Use a queue-specific Consumer class that overrides getExchangeName().");
		}
		
		/**
		 * Provides the queue name for this class
		 * @return string
		 */
		public function getQueueName(): string {
			throw new Exception("Do not call the base Consumer class directly. Use a queue-specific Consumer class that overrides getQueueName().");
		}
		
		/**
		 * Process a message from this queue
		 * @param Message $message A decoded message from this queue
		 * @return void
		 */
		public function process(Message $message): void {
			throw new Exception("Do not call the base Consumer class directly. Use a queue-specific Consumer class.");
		}
		
		
		public function getConsumerTag(): string {
			return $this->consumer_tag;
		}
	}