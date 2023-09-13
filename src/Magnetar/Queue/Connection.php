<?php
	declare(strict_types=1);
	
	namespace Magnetar\Queue;
	
	use Magnetar\Queue\Exceptions\ConnectionException;
	
	/**
	 * Base Connection class
	 */
	class Connection {
		/**
		 * A simple array of names of declared queues
		 * @var array
		 */
		protected array $declared_queues = [];
		
		public function __construct(
			protected string $host,
			protected int $port,
			protected ?string $user=null,
			protected ?string $pass=null,
		) {
			$this->makeHandler();
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
		
		/**
		 * Called by Channel to create a new channel queue
		 * @param string $queue_name Name of the queue to create
		 * @return void
		 * 
		 * @throws ConnectionException
		 */
		public function declareQueue(string $queue_name): void {
			// adapter-specific implementation should call this parent method to keep track of declared queues
			
			$this->declared_queues[] = $queue_name;
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
	}