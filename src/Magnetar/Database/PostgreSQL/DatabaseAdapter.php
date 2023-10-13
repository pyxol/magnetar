<?php
	declare(strict_types=1);
	
	namespace Magnetar\Database\PostgreSQL;
	
	use RuntimeException;
	
	use Magnetar\Database\DatabaseAdapter as BaseDatabaseAdapter;
	use Magnetar\Database\Exceptions\DatabaseAdapterException;
	
	/**
	 * Database adapter for PostgreSQL
	 */
	class DatabaseAdapter extends BaseDatabaseAdapter {
		/**
		 * {@inheritDoc}
		 */
		const ADAPTER_NAME = 'postgresql';
		
		/**
		 * {@inheritDoc}
		 */
		protected function validateRuntime(): void {
			parent::validateRuntime();
			
			if(!isset($this->connection_config['host'])) {
				throw new DatabaseAdapterException("Database configuration is missing host");
			}
			
			if(!isset($this->connection_config['port'])) {
				throw new DatabaseAdapterException("Database configuration is missing port");
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
			
			// check if the PDO PostgreSQL extension is loaded
			if(!extension_loaded('pdo_pgsql')) {
				throw new RuntimeException("The PDO PostgreSQL extension (pdo_pgsql) is not loaded");
			}
		}
		
		/**
		 * {@inheritDoc}
		 */
		protected function generateDSN(): string {
			return 'pgsql:host='. $this->connection_config['host'] .';'. ((!empty($this->connection_config['port']) && is_numeric($this->connection_config['port']))?'port='. $this->connection_config['port'] .';':'') .'dbname='. $this->connection_config['database'];
		}
		
		/**
		 * {@inheritDoc}
		 */
		protected function postConnection(): void {
			parent::postConnection();
			
			// optional charset settings
			if(isset($this->connection_config['charset'])) {
				//$this->pdo->prepare("SET CLIENT_ENCODING TO ?")->execute([$this->connection_config['charset']]);
				$this->dbh->prepare("SET NAMES ?")->execute([$this->connection_config['charset']]);
			}
		}
	}