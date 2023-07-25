<?php
	declare(strict_types=1);
	
	namespace Magnetar\Database\PostgreSQL;
	
	use PDO;
	use RuntimeException;
	
	use Magnetar\Database\AbstractDatabaseAdapter;
	use Magnetar\Database\QuickQueryInterface;
	use Magnetar\Database\DatabaseAdapterException;
	
	class DatabaseAdapter extends AbstractDatabaseAdapter implements QuickQueryInterface {
		protected string $adapter_name = 'postgresql';
		
		// PDO instance
		protected ?PDO $pdo = null;
		
		/**
		 * Validate configuration
		 * @param array $configuration Configuration data to validate
		 * 
		 * @throws DatabaseAdapterException
		 */
		protected function validateConfig(array $configuration): void {
			if(!isset($configuration['host'])) {
				throw new DatabaseAdapterException("Database configuration is missing host");
			}
			
			if(!isset($configuration['port'])) {
				throw new DatabaseAdapterException("Database configuration is missing port");
			}
			
			if(!isset($configuration['user'])) {
				throw new DatabaseAdapterException("Database configuration is missing user");
			}
			
			if(!isset($configuration['password'])) {
				throw new DatabaseAdapterException("Database configuration is missing password");
			}
			
			if(!isset($configuration['database'])) {
				throw new DatabaseAdapterException("Database configuration is missing database");
			}
		}
		
		/**
		 * Start the database-specific connection
		 * @param array $configuration The application container
		 * @return void
		 * 
		 * @throws RuntimeException
		 * @throws DatabaseAdapterException
		 */
		protected function wireUp(
			array $configuration=[]
		): void {
			if(!extension_loaded('pdo_pgsql')) {
				throw new RuntimeException("The PDO PostgreSQL extension (pdo_pgsql) is not loaded");
			}
			
			// pull the configuration and check if it is valid
			$this->validateConfig($configuration);
			
			// PDO options
			$default_options = [
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			];
			
			// connect to the database
			$this->pdo = new PDO(
				"pgsql:host=". $configuration['host'] .";". ((!empty($configuration['port']) && is_numeric($configuration['port']))?"port=". $configuration['port'] .";":"") ."dbname=". $configuration['database'],
				$configuration['user'],
				$configuration['password'],
				$default_options
			);
			
			// optional charset settings
			if(isset($config['charset'])) {
				$this->pdo->exec("SET NAMES ". $config['charset']);
			}
		}
		
		/**
		 * Run a query. Generally used for anything besides SELECT statements. If an INSERT query, return the last insert ID, otherwise return the number of rows affected. Returns false on failure
		 * @param string $sql_query The SQL query to run. If used in conjunction with $params, use either named (:var) or unnamed placeholders (?), not both
		 * @param array $params The parameters to bind to the query. Named params (:var) require an assoc array, unnamed (?) require a consecutive array
		 * @return int|false
		 * 
		 * @see https://www.php.net/manual/en/pdo.exec.php
		 * @see https://www.php.net/manual/en/pdo.prepare.php
		 * 
		 * @example $db->query("INSERT INTO `table` (`column`, `column2`) VALUES (:value, :value2)", ['value' => 'test', 'value2' => 'test2']);
		 * @example $db->query("INSERT INTO `table` (`column`, `column2`) VALUES (?, ?)", ['test', 'test2']);
		 * @example $db->query("INSERT INTO `table` (`column`, `column2`) VALUES ('test', 'test2')");
		 */
		public function query(string $sql_query, array $params=[]): int|false {
			if(!empty($params)) {
				// prepare the query
				$statement = $this->pdo->prepare($sql_query);
				
				// execute the query
				$result = $statement->execute($params);
			} else {
				// execute the query
				$result = $this->pdo->exec($sql_query);
			}
			
			if(preg_match("#^\s*INSERT#si", $sql_query)) {
				return $this->pdo->lastInsertId();
			}
			
			// return the result
			return $result;
		}
		
		/**
		 * Run a query and return an array of rows. Generally used for SELECT statements. Returns false on failure
		 * @param string $sql_query The SQL query to run
		 * @param array $params The parameters to bind to the query
		 * @param string|false $column_key The column to use as the array key for the results. If false, use an incrementing integer
		 * @return array|false
		 * 
		 * @see \Magnetar\Database\PostgreSQL::query() for use of $sql_query and $params
		 */
		public function get_rows(
			string $sql_query,
			array $params=[],
			string|false $column_key=false
		): array|false {
			// prepare the query
			$statement = $this->pdo->prepare($sql_query);
			
			if(!empty($params)) {
				// execute the query with params
				$statement->execute($params);
			} else {
				// execute the query
				$statement->execute();
			}
			
			$rows = [];
			
			if($statement->rowCount()) {
				while($row = $statement->fetch(PDO::FETCH_ASSOC)) {
					if((false !== $column_key) && isset($row[ $column_key ])) {
						$rows[ $row[ $column_key ] ] = $row;
					} else {
						$rows[] = $row;
					}
				}
			}
			
			return $rows;
		}
		
		/**
		 * Run a query and return a single row. Generally used for SELECT statements. Returns false on failure
		 * @param string $sql_query The SQL query to run
		 * @param array $params The parameters to bind to the query
		 * @return array|false
		 * 
		 * @see \Magnetar\Database\PostgreSQL::query() for $params usage with $sql_query
		 */
		public function get_row(string $sql_query, array $params=[]): array|false {
			// prepare the query
			$statement = $this->pdo->prepare($sql_query);
			
			if(!empty($params)) {
				// execute the query with params
				$statement->execute($params);
			} else {
				// execute the query
				$statement->execute();
			}
			
			if(!$statement->rowCount()) {
				return false;
			}
			
			return $statement->fetch(PDO::FETCH_ASSOC);
		}
		
		/**
		 * Run a query and return an array of a single column. Generally used for SELECT statements. Returns false on failure
		 * @param string $sql_query The SQL query to run
		 * @param array $params The parameters to bind to the query
		 * @param string|int $column_key The column to use as the array key for the results
		 * @return array|false
		 * 
		 * @see \Magnetar\Database\PostgreSQL::query() for $params usage with $sql_query
		 */
		public function get_col(
			string $sql_query,
			array $params=[],
			string|int $column_key=0
		): array|false {
			// prepare the query
			$statement = $this->pdo->prepare($sql_query);
			
			if(!empty($params)) {
				// execute the query with params
				$statement->execute($params);
			} else {
				// execute the query
				$statement->execute();
			}
			
			$rows = [];
			
			if($statement->rowCount()) {
				while($row = $statement->fetch(PDO::FETCH_ASSOC)) {
					if(isset($row[ $column_key ])) {
						$rows[] = $row[ $column_key ];
					} else {
						$rows[] = array_shift($row);
					}
				}
			}
			
			return $rows;
		}
		
		/**
		 * Run a query and return an array of a single column. Generally used for SELECT statements. Returns false on failure
		 * @param string $sql_query The SQL query to run
		 * @param array $params The parameters to bind to the query
		 * @param string|int $assoc_key The column to use as the array key for the results. Defaults to first column
		 * @param string|int $column_key The column to use as the array value for the results. Defaults to second column
		 * @return array|false
		 * 
		 * @see \Magnetar\Database\PostgreSQL::query() for $params usage with $sql_query
		 */
		public function get_col_assoc(
			string $sql_query,
			array $params=[],
			string|int $assoc_key=0,
			string|int $column_key=1
		): array|false {
			// prepare the query
			$statement = $this->pdo->prepare($sql_query);
			
			if(!empty($params)) {
				// execute the query with params
				$statement->execute($params);
			} else {
				// execute the query
				$statement->execute();
			}
			
			$rows = [];
			
			if($statement->rowCount()) {
				$assoc_col = null;
				$value_col = null;
				
				while($row = $statement->fetch(PDO::FETCH_ASSOC)) {
					// determine key column
					if(is_null($assoc_col)) {
						if(isset($row[ $assoc_key ]) && ($assoc_key !== $column_key)) {
							$assoc_col = $assoc_key;
						} else {
							//$assoc_possibilities = array_keys($row);
							//$assoc_col = array_shift($assoc_possibilities);
							$assoc_col = false;
						}
					}
					
					// determine value column
					if(is_null($value_col)) {
						if(isset($row[ $column_key ])) {
							$value_col = $column_key;
						} else {
							$assoc_possibilities = array_keys($row);
							$value_col = array_pop($assoc_possibilities);
						}
					}
					
					// assign rows
					if(false !== $assoc_col) {
						$rows[ $row[ $assoc_col ] ] = $row[ $value_col ];
					} else {
						$rows[] = $row[ $value_col ];
					}
				}
			}
			
			return $rows;
		}
		
		/**
		 * Run a query and return a single value. Generally used for SELECT statements. Returns false on failure
		 * @param string $sql_query The SQL query to run
		 * @param array $params The parameters to bind to the query
		 * @param string|false $column_key The column to use as the array key for the results. Leaving this as False uses the first column specified in the query
		 * @return string|int|false
		 * 
		 * @see \Magnetar\Database\PostgreSQL::query() for $params usage with $sql_query
		 */
		public function get_var(
			string $sql_query,
			array $params=[],
			string|int|false $column_key=false
		): string|int|false {
			// prepare the query
			$row = $this->get_row($sql_query, $params);
			
			if(false !== $column_key) {
				if(isset($row[ $column_key ])) {
					return $row[ $column_key ];
				}
				
				return false;
			}
			
			return array_shift($row);
		}
	}