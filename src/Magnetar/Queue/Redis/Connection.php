<?php
	declare(strict_types=1);
	
	namespace Magnetar\Queue\Redis;
	
	use Magnetar\Queue\Connection as BaseConnection;
	
	/**
	 * Connection class for Redis
	 * 
	 * @todo update to use redis library
	 */
	class Connection extends BaseConnection {
		/**
		 * {@inheritDoc}
		 */
		public function makeHandler(): mixed {
			return new \Redis(
				$this->host,
				$this->port
			);
		}
	}