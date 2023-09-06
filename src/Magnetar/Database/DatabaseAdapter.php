<?php
	declare(strict_types=1);
	
	namespace Magnetar\Database;
	
	use RuntimeException;
	
	use Magnetar\Database\Exceptions\DatabaseAdapterException;
	
	/**
	 * Database adapter
	 */
	class DatabaseAdapter {
		/**
		 * Name of the adapter
		 */
		const ADAPTER_NAME = '';
		
		/**
		 * Adapter constructor
		 * @param string $connection_name Name of the connection
		 * @param array $configuration Configuration data to wire up the connection
		 * 
		 * @throws RuntimeException
		 * @throws DatabaseAdapterException
		 */
		public function __construct(
			protected string $connection_name,
			protected array $connection_config = []
		) {
			// wire up to DB instance
			$this->wireUp();
		}
		
		/**
		 * Start the connection
		 * @return void
		 * 
		 * @throws RuntimeException
		 * @throws DatabaseAdapterException
		 */
		protected function wireUp(): self {
			// pull the configuration and check if it is valid
			$this->validateRuntime();
			
			// create the connection
			$this->createConnection();
			
			// run any post connection actions
			$this->postConnection();
			
			return $this;
		}
		
		/**
		 * Validate runtime configuration
		 * return void
		 * 
		 * @throws RuntimeException
		 * @throws DatabaseAdapterException
		 */
		protected function validateRuntime(): void {
			// individual adapters are encouraged to override this method
		}
		
		/**
		 * Create the connection to the database
		 * @return void
		 */
		protected function createConnection(): void {
			// individual adapters should override this method
			throw new DatabaseAdapterException("Do not use the base DatabaseAdapter class directly. Use a specific adapter instead.");
		}
		
		/**
		 * Post connection actions (typically character set)
		 * @return void
		 */
		protected function postConnection(): void {
			// individual adapters may override this method
		}
		
		/**
		 * Run a standard query. Returns the last inserted ID if an INSERT query is used, the number of affected rows, or false on error
		 * @param string $sql_query The SQL query to run
		 * @param array $params Optional. Parameters to bind to the query
		 * @return int|false The last inserted ID, the number of affected rows, or false on error
		 * 
		 * @throws RuntimeException
		 */
		public function query(string $sql_query, array $params=[]): int|false {
			throw new RuntimeException("Do not use the base DatabaseAdapter class directly. Use a specific adapter instead.");
			
			return false;
		}
		
		/**
		 * Get the adapter name
		 * @return string The name of the adapter
		 */
		public function getAdapterName(): string {
			return self::ADAPTER_NAME;
		}
	}