<?php
	declare(strict_types=1);
	
	namespace Magnetar\Database\SQLServer;
	
	use RuntimeException;
	
	use Magnetar\Database\DatabaseAdapter as BaseDatabaseAdapter;
	use Magnetar\Database\Exceptions\DatabaseAdapterException;
	
	/**
	 * Database adapter for SQL Server
	 */
	class DatabaseAdapter extends BaseDatabaseAdapter {
		const ADAPTER_NAME = 'sqlserver';
		
		/**
		 * {@inheritDoc}
		 */
		protected function validateRuntime(): void {
			parent::validateRuntime();
			
			if(!isset($this->connection_config['host'])) {
				throw new DatabaseAdapterException("Database configuration is missing host");
			}
			
			//if(!isset($this->connection_config['user'])) {
			//	throw new DatabaseAdapterException("Database configuration is missing user");
			//}
			//
			//if(!isset($this->connection_config['password'])) {
			//	throw new DatabaseAdapterException("Database configuration is missing password");
			//}
			
			if(!isset($this->connection_config['database'])) {
				throw new DatabaseAdapterException("Database configuration is missing database");
			}
			
			// check if the PDO SQLSRV extension is loaded
			if(!extension_loaded('pdo_sqlsrv')) {
				throw new RuntimeException("The PDO SQLServer extension (pdo_sqlsrv) is not loaded");
			}
		}
		
		/**
		 * {@inheritDoc}
		 */
		protected function generateDSN(): string {
			return 'sqlsrv:server='. $this->connection_config['host'] .';Database='. $this->connection_config['database'];
		}
		
		/**
		 * {@inheritDoc}
		 */
		protected function postConnection(): void {
			parent::postConnection();
			
			// optional charset settings
			if(isset($config['charset'])) {
				//$this->dbh->exec("SET NAMES ". $config['charset']);
				$this->dbh->prepare("SET NAMES ?")->execute([$config['charset']]);
			}
		}
	}