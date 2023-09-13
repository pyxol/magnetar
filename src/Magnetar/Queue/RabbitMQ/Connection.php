<?php
	declare(strict_types=1);
	
	namespace Magnetar\Queue\RabbitMQ;
	
	use Magnetar\Queue\Connection as BaseConnection;
	
	use PhpAmqpLib\Connection\AMQPStreamConnection;
	use PhpAmqpLib\Channel\AMQPChannel;
	
	/**
	 * Connection class for RabbitMQ
	 */
	class Connection extends BaseConnection {
		protected AMQPStreamConnection $qcon;
		protected AMQPChannel $qch;
		
		/**
		 * {@inheritDoc}
		 */
		protected array $declared_queues = [];
		
		/**
		 * {@inheritDoc}
		 */
		public function connect(): mixed {
			$this->qcon = new \PhpAmqpLib\Connection\AMQPStreamConnection(
				$this->host,
				$this->port,
				$this->user,
				$this->pass
			);
			
			$this->qch = $this->qcon->channel();
		}
		
		/**
		 * {@inheritDoc}
		 */
		public function declareQueue(string $queue_name): void {
			if(in_array($queue_name, $this->declared_queues)) {
				return;
			}
			
			$this->qch->queue_declare(
				$queue_name,
				false,
				false,
				false,
				false
			);
			
			parent::declareQueue($queue_name);
		}
	}