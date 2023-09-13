<?php
	declare(strict_types=1);
	
	namespace Magnetar\Queue\RabbitMQ;
	
	use PhpAmqpLib\Message\AMQPMessage;
	
	use Magnetar\Queue\Message as BaseMessage;
	use Magnetar\Utilities\JSON;
	
	/**
	 * Message class for RabbitMQ queues
	 * 
	 * @todo override the contentType method with $this->original->get('content_type') or $this->original->getContentEncoding() ?
	 */
	class Message extends BaseMessage {
		public function __construct(
			protected AMQPMessage $original
		) {
			parent::__construct($original);
		}
		
		/**
		 * {@inheritDoc}
		 */
		protected function parseMessage(): void {
			$this->decodeBody();
		}
		
		/**
		 * Decode the message body
		 * @return mixed The decoded message body
		 * 
		 * @uses JSON::maybe_decode to decode the message body if it is JSON
		 */
		protected function decodeBody(): mixed {
			$this->body = JSON::maybe_decode($this->original->getBody());
		}
		
		/**
		 * {@inheritDoc}
		 */
		public function ack(): void {
			$this->original->ack();
			
			parent::ack();
		}
		
		/**
		 * {@inheritDoc}
		 */
		public function nack(): void {
			$this->original->nack();
			
			parent::nack();
		}
		
		/**
		 * {@inheritDoc}
		 */
		public function requeue(): void {
			$this->original->reject(true);
			
			parent::requeue();
		}
		
		/**
		 * {@inheritDoc}
		 */
		public function reject(): void {
			$this->original->reject(false);
			
			parent::reject();
		}
	}
	