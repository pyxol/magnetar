<?php
	declare(strict_types=1);
	
	namespace Magnetar\Queue;
	
	use Exception;
	use Magnetar\Queue\Enums\AcknowledgementEnum;
	use Magnetar\Utilities\JSON;
	
	/**
	 * Message class for queues
	 * 
	 * @todo decouple from RabbitMQ/AMQP
	 * @todo make use of Magnetar\Queue\Channel
	 */
	class Message {
		/**
		 * The body of the message
		 * @var mixed
		 */
		protected mixed $body = null;
		
		/**
		 * Acknowledgement status of this message
		 * @var string|null
		 * @uses AcknowledgementEnum
		 */
		protected ?string $acknowledgement = null;
		
		/**
		 * Construction method
		 * @param mixed $original A message resource received from a message queue extension
		 */
		public function __construct(
			protected mixed $original
		) {
			// parse the message
			$this->parseMessage();
		}
		
		/**
		 * Parse the raw message resource from the queue
		 * @return void
		 * 
		 * @throws Exception
		 */
		protected function parseMessage(): void {
			throw new Exception("Do not use the base Message class directly. Use a queue-specific Message class.");
		}
		
		/**
		 * Get a parsed body of the message
		 * @return string
		 */
		public function body(): mixed {
			return $this->body;
		}
		
		/**
		 * Encode the message body
		 * @return mixed The encoded message body
		 */
		public function encode(): mixed {
			return JSON::maybe_encode($this->body);
		}
		
		/**
		 * Get the content type of the message
		 * @return string The content type
		 */
		public function contentType(): string {
			return 'text/plain';
		}
		
		/**
		 * See if this message has been handled (acknowledged or rejected or released already)
		 * @return bool
		 */
		public function isHandled(): bool {
			return (null !== $this->acknowledgement);
		}
		
		/**
		 * Set the acknowledgement status of this message
		 * @param AcknowledgementEnum $acknowledgement The acknowledgement status of this message
		 * @return void
		 */
		protected function setAcknowledgement(AcknowledgementEnum $acknowledgement): void {
			$this->acknowledgement = $acknowledgement;
		}
		
		/**
		 * Acknowledge the message as processed
		 * @return void
		 */
		public function ack(): void {
			// adapter message class should also call this (eg: parent::ack())
			
			$this->setAcknowledgement(AcknowledgementEnum::ACK);
		}
		
		/**
		 * Negatively Acknowledge the message
		 * @return void
		 */
		public function nack(): void {
			// adapter message class should also call this (eg: parent::nack())
			
			$this->setAcknowledgement(AcknowledgementEnum::NACK);
		}
		
		/**
		 * Requeue the message back to the queue
		 * @return void
		 */
		public function requeue(): void {
			// adapter message class should also call this (eg: parent::requeue())
			
			$this->setAcknowledgement(AcknowledgementEnum::REQUEUE);
		}
		
		/**
		 * Reject this message, deleting it from the queue
		 * @return void
		 */
		public function reject(): void {
			// adapter message class should also call this (eg: parent::reject())
			
			$this->setAcknowledgement(AcknowledgementEnum::REJECT);
		}
	}