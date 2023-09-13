<?php
	declare(strict_types=1);
	
	namespace Magnetar\Queue;
	
	use Magnetar\Queue\Exceptions\ConnectionException;
	
	/**
	 * Base Connection class
	 */
	class Connection {
		public function __construct(
			protected string $host,
			protected int $port,
			protected ?string $user=null,
			protected ?string $pass=null,
		) {
			
		}
		
		/**
		 * Get the host for this connection
		 * @return string
		 */
		public function getHost(): string {
			return $this->host;
		}
		
		/**
		 * Get the port for this connection
		 * @return int
		 */
		public function getPort(): int {
			return $this->port;
		}
		
		/**
		 * Get the user for this connection
		 * @return string|null
		 */
		public function getUser(): string|null {
			return $this->user;
		}
		
		/**
		 * Get the password for this connection
		 * @return string|null
		 */
		public function getPass(): string|null {
			return $this->pass;
		}
		
		/**
		 * Make the resource handler for this connection
		 * @return mixed
		 * 
		 * @throws ConnectionException if the handler could not be created
		 */
		public function makeHandler(): mixed {
			throw new ConnectionException("Do not use the base DatabaseAdapter class directly. Use a specific adapter instead.");
		}
	}