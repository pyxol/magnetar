<?php
	declare(strict_types=1);
	
	namespace Magnetar\Database;
	
	use \Exception;
	
	use Magnetar\Container\Container;
	use Magnetar\Database\DatabaseAdapterException;
	
	abstract class AbstractDatabaseAdapter implements DatabaseAdapterInterface {
		protected string $adapter_name = '';
		
		/**
		 * Connect to a MariaDB database
		 * @param array $configuration Configuration to pass to the database adapter
		 * 
		 * @throws Exception
		 */
		public function __construct(
			protected string $connection_name,
			Container $container
		) {
			// wire up to DB instance
			$this->wireUp($container);
		}
		
		/**
		 * Wire up to the DB instance
		 * @param Container $container
		 */
		abstract protected function wireUp(Container $container): void;
		
		/**
		 * Returns the name of the adapter
		 * @return string
		 */
		public function getAdapterName(): string {
			return $this->adapter_name;
		}
	}