<?php
	declare(strict_types=1);
	
	namespace Magnetar\Database\SQLite;
	
	use RuntimeException;
	use PDO;
	
	use Magnetar\Database\DatabaseAdapter as BaseDatabaseAdapter;
	use Magnetar\Database\HasPDOTrait;
	use Magnetar\Database\HasQuickQueryTrait;
	use Magnetar\Database\HasQueryBuilderTrait;
	use Magnetar\Database\Exceptions\DatabaseAdapterException;
	
	class DatabaseAdapter extends BaseDatabaseAdapter {
		use HasPDOTrait,
			HasQuickQueryTrait,
			HasQueryBuilderTrait;
		
		const ADAPTER_NAME = 'sqlite3';
		
		/**
		 * {@inheritDoc}
		 */
		protected function validateRuntime(): void {
			parent::validateRuntime();
			
			if(!isset($this->connection_config['database'])) {
				throw new DatabaseAdapterException("Database configuration is missing database");
			}
			
			//if(!isset($this->connection_config['user'])) {
			//	throw new DatabaseAdapterException("Database configuration is missing user");
			//}
			//
			//if(!isset($this->connection_config['password'])) {
			//	throw new DatabaseAdapterException("Database configuration is missing password");
			//}
			
			// check if the PDO SQLite extension is loaded
			if(!extension_loaded('pdo_sqlite')) {
				throw new RuntimeException("The PDO SQLite extension (pdo_sqlite) is not loaded");
			}
		}
		
		/**
		 * {@inheritDoc}
		 */
		protected function generateDSN(): string {
			return 'sqlite:'. $this->connection_config['database'];
		}
		
		/**
		 * {@inheritDoc}
		 */
		protected function postConnection(): void {
			parent::postConnection();
			
			//// optional charset settings
			//if(isset($config['charset'])) {
			//	$this->pdo->exec("SET NAMES ". $config['charset']);
			//	$this->pdo->exec("SET CHARACTER SET ". $config['charset']);
			//}
		}
	}