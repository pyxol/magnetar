<?php
	declare(strict_types=1);
	
	namespace Magnetar\Queue\Redis;
	
	use RuntimeException;
	
	use Magnetar\Queue\QueueAdapter as BaseQueueAdapter;
	use Magnetar\Queue\Exceptions\QueueAdapterException;
	use Magnetar\Queue\Redis\Connection;
	use Magnetar\Queue\Redis\Message;
	use Magnetar\Queue\Redis\Channel;
	
	/**
	 * Message Queue adapter for Redis
	 */
	class QueueAdapter extends BaseQueueAdapter {
		const ADAPTER_NAME = 'redis';
		
		/**
		 * {@inheritDoc}
		 */
		protected function validateRuntime(): void {
			parent::validateRuntime();
			
			// check if the library for Redis is installed
			if(!class_exists('Redis')) {
				throw new RuntimeException('The required Redis extension was not found');
			}
			
			if(!isset($this->connection_config['host'])) {
				throw new QueueAdapterException('Queue configuration is missing host');
			}
			
			if(!isset($this->connection_config['port'])) {
				throw new QueueAdapterException('Queue configuration is missing port');
			}
			
			//if(!isset($this->connection_config['user'])) {
			//	throw new QueueAdapterException('Queue configuration is missing user');
			//}
			//
			//if(!isset($this->connection_config['password'])) {
			//	throw new QueueAdapterException('Queue configuration is missing password');
			//}
		}
		
		/**
		 * {@inheritDoc}
		 */
		protected function createConnection(): void {
			$this->connection = new Connection(
				$this->connection_config['host'],
				$this->connection_config['port'],
			);
			
			$this->qh = $this->connection->makeHandler();
		}
		
		/**
		 * {@inheritDoc}
		 * 
		 * @todo post-connection actions
		 */
		protected function postConnection(): void {
			parent::postConnection();
			
			// post-connection actions...
		}
		
		/**
		 * {@inheritDoc}
		 * 
		 * @todo vscode reports as incompatible return type
		 * @todo implement this
		 */
		public function sendMessage(
			Channel $channel,
			Message $message,
			string $exchange = ''
		): bool {
			//$message = new AMQPMessage(
			//	$message->encodeBody(),
			//	[
			//		'content_type' => $message->getContentType(),
			//	]
			//);
			//
			//$this->channel->basic_publish($message, $exchange);
			
			return true;
		}
	}