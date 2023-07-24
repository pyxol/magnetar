<?php
	declare(strict_types=1);
	
	namespace Magnetar\Database;
	
	use \Exception;
	
	abstract class AbstractDatabaseAdapter implements DatabaseAdapterInterface {
		protected string $adapter_name = '';
		
		/**
		 * Adapter constructor
		 * @param string $connection_name Name of the connection
		 * @param array $configuration Configuration data to wire up the connection
		 * 
		 * @throws Exception
		 */
		public function __construct(
			protected string $connection_name,
			array $configuration = []
		) {
			// wire up to DB instance
			$this->wireUp($configuration);
		}
		
		/**
		 * Wire up to the instance
		 * @param array $connection_config Connection-specific configuration
		 */
		abstract protected function wireUp(array $connection_config): void;
		
		/**
		 * Returns the name of the adapter
		 * @return string
		 */
		public function getAdapterName(): string {
			return $this->adapter_name;
		}
	}