<?php
	declare(strict_types=1);
	
	namespace Magnetar\Queue\Redis;
	
	use Redis;
	
	use Magnetar\Queue\Message as BaseMessage;
	use Magnetar\Utilities\JSON;
	
	/**
	 * Message class for Redis queues
	 * 
	 * @todo needs heavy work
	 */
	class Message extends BaseMessage {
		/**
		 * {@inheritDoc}
		 */
		protected function parseMessage(mixed $message): void {
			$this->decodeBody($message);
		}
		
		/**
		 * Decode the message body
		 * @return mixed The decoded message body
		 */
		protected function decodeBody(mixed $body): mixed {
			return JSON::maybe_decode($body->message ?? $body ?? '');
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
	