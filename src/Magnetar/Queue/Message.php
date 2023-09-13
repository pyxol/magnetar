<?php
	declare(strict_types=1);
	
	namespace Magnetar\Queue;
	
	use Exception;
	use Magnetar\Queue\Enums\AcknowledgementEnum;
	use Magnetar\Queue\Channel;
	use Magnetar\Utilities\JSON;
	
	/**
	 * Message class for queues
	 * 
	 * @todo decouple from RabbitMQ/AMQP
	 * @todo make use of Magnetar\Queue\Channel
	 */
	class Message {
		/**
		 * Acknowledgement status of this message
		 * @var string|null
		 * @uses AcknowledgementEnum
		 */
		protected ?string $acknowledgement = null;
		
		/**
		 * The body of the message
		 * @var mixed
		 */
		protected mixed $body = null;
		
		/**
		 * Construction method
		 * @param Channel The queue channel this message belongs to
		 * @param mixed $message
		 */
		public function __construct(
			protected Channel $channel,
			protected mixed $message
		) {
			// parse the message
			$this->parseMessage();
		}
		
		/**
		 * Parse the message from the queue
		 * @return void
		 * @throws Exception
		 */
		protected function parseMessage(): void {
			//throw new Exception("Do not use the base Message class directly. Use a queue-specific Message class.");
			
			$this->decodeBody();
		}
		
		/**
		 * Decode the message body
		 * @return mixed The decoded message body
		 */
		protected function decodeBody(mixed $body=null): mixed {
			return JSON::maybe_decode($body ?? $this->message->body);
		}
		
		/**
		 * Encode the message body
		 * @return mixed The encoded message body
		 */
		public function encodeBody(mixed $body=null): mixed {
			return JSON::maybe_encode($body ?? $this->body);
		}
		
		/**
		 * Get the content type of the message
		 * @return string
		 */
		public function getContentType(): string {
			return 'text/plain';
		}
		
		/**
		 * Get the raw message body
		 * @return string
		 */
		public function getRawBody(): mixed {
			return $this->message->body;
		}
		
		/**
		 * Get a parsed body of the message
		 * @return string
		 * @uses JSON::maybe_decode to decode the message body if it is JSON
		 */
		public function getBody(): mixed {
			return JSON::maybe_decode($this->message->body);
		}
		
		/**
		 * See if this message has been handled (acknowledged or rejected or released already)
		 * @return bool
		 */
		public function isHandled(): bool {
			return (null !== $this->acknowledgement);
		}
		
		/**
		 * Acknowledge the message as processed
		 * @return void
		 */
		public function ack(): void {
			if($this->isHandled()) {
				return;
			}
			
			$this->message->ack();
			
			$this->acknowledgement = AcknowledgementEnum::ACK;
		}
		
		/**
		 * Release the message back to the queue
		 * @return void
		 */
		public function release(): void {
			if($this->isHandled()) {
				return;
			}
			
			// requeue
			$this->message->reject(true);
			
			$this->acknowledgement = AcknowledgementEnum::REQUEUE;
		}
		
		/**
		 * Reject this message, deleting it from the queue
		 * @return void
		 */
		public function reject(): void {
			if($this->isHandled()) {
				return;
			}
			
			$this->message->reject(false);
			
			$this->acknowledgement = AcknowledgementEnum::REJECT;
		}
		
		/**
		 * Get the exchange
		 * @return string
		 */
		public function getExchange(): string {
			return $this->message->getExchange();
		}
		
		/**
		 * Get the channel
		 * @return Channel
		 */
		public function getChannel(): Channel {
			return $this->channel;
		}
		
		/**
		 * Get the delivery tag
		 * @return string
		 */
		public function getDeliveryTag(): int {
			return $this->message->getDeliveryTag();
		}
		
		/**
		 * Get the consumer tag
		 * @return string
		 */
		public function getConsumerTag(): string {
			return $this->message->getConsumerTag();
		}
		
		/**
		 * Get the routing key
		 * @return string
		 */
		public function getRoutingKey(): string {
			return $this->message->getRoutingKey();
		}
	}