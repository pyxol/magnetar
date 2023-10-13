<?php
	declare(strict_types=1);
	
	namespace Magnetar\Database;
	
	use RuntimeException;
	
	use Magnetar\Database\HasPDOTrait;
	use Magnetar\Database\HasQuickQueryTrait;
	use Magnetar\Database\HasQueryBuilderTrait;
	use Magnetar\Database\Exceptions\DatabaseAdapterException;
	
	/**
	 * Database adapter
	 */
	class DatabaseAdapter {
		use HasPDOTrait,
			HasQuickQueryTrait,
			HasQueryBuilderTrait;
		
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
		 * @return void
		 * 
		 * @throws RuntimeException
		 * @throws DatabaseAdapterException
		 */
		protected function validateRuntime(): void {
			// individual adapters are encouraged to override this method
		}
		
		/**
		 * Post connection actions (typically character set)
		 * @return void
		 */
		protected function postConnection(): void {
			// individual adapters may override this method
		}
		
		/**
		 * Get the adapter name
		 * @return string The name of the adapter
		 */
		public function getAdapterName(): string {
			return self::ADAPTER_NAME;
		}
	}