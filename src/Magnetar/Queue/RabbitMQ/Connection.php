<?php
	declare(strict_types=1);
	
	namespace Magnetar\Queue\RabbitMQ;
	
	use Magnetar\Queue\Connection as BaseConnection;
	
	/**
	 * Connection class for RabbitMQ
	 */
	class Connection extends BaseConnection {
		/**
		 * {@inheritDoc}
		 */
		public function makeHandler(): mixed {
			return new \PhpAmqpLib\Connection\AMQPStreamConnection(
				$this->host,
				$this->port,
				$this->user,
				$this->pass
			);
		}
	}