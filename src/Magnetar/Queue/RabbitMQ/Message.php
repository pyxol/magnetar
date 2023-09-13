<?php
	declare(strict_types=1);
	
	namespace Magnetar\Queue\RabbitMQ;
	
	use PhpAmqpLib\Message\AMQPMessage;
	
	use Magnetar\Queue\Message as BaseMessage;
	use Magnetar\Utilities\JSON;
	
	/**
	 * Message class for RabbitMQ queues
	 */
	class Message extends BaseMessage {
		/**
		 * {@inheritDoc}
		 */
		protected function parseMessage(): void {
			
		}
		
		/**
		 * {@inheritDoc}
		 */
		public function getRawBody(): mixed {
			return $this->message->body ?? null;
		}
		
		/**
		 * {@inheritDoc}
		 */
		public function getBody(): mixed {
			return JSON::maybe_decode($this->getRawBody());
		}
	}
	