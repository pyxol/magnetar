<?php
	declare(strict_types=1);
	
	namespace Magnetar\Database;
	
	use \Exception;
	
	use Magnetar\Container\Container;
	use Magnetar\Database\DatabaseAdapterException;
	
	abstract class AbstractDatabaseAdapter implements DatabaseAdapterInterface {
		/**
		 * Connect to a MariaDB database
		 * @param array $configuration Configuration to pass to the database adapter
		 * 
		 * @throws Exception
		 */
		public function __construct(Container $container) {
			// wire up to DB instance
			$this->wireUp($container);
		}
		
		/**
		 * Wire up to the DB instance
		 * @param Container $container
		 */
		abstract protected function wireUp(Container $container): void;
		
		/**
		 * Throws an exception if configuration for this adapter is invalid
		 * @param array $config_data
		 * 
		 * @throws DatabaseAdapterException
		 */
		protected function throwIfInvalidConfig(array $config_data): void {
			if(!isset($config_data['host'])) {
				throw new DatabaseAdapterException("Database configuration is missing host");
			}
			
			if(!isset($config_data['port'])) {
				throw new DatabaseAdapterException("Database configuration is missing port");
			}
			
			if(!isset($config_data['user'])) {
				throw new DatabaseAdapterException("Database configuration is missing user");
			}
			
			if(!isset($config_data['password'])) {
				throw new DatabaseAdapterException("Database configuration is missing password");
			}
			
			if(!isset($config_data['database'])) {
				throw new DatabaseAdapterException("Database configuration is missing database");
			}
		}
	}