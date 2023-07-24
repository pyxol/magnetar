<?php
	declare(strict_types=1);
	
	namespace Magnetar\Database\SQLite;
	
	use SQLite3;
	use RuntimeException;
	
	use Magnetar\Database\AbstractDatabaseAdapter;
	use Magnetar\Database\QuickQueryInterface;
	use Magnetar\Database\DatabaseAdapterException;
	use Magnetar\Database\QueryPreperationException;
use SQLite3Result;
use SQLite3Stmt;

	class DatabaseAdapter extends AbstractDatabaseAdapter implements QuickQueryInterface {
		protected string $adapter_name = 'sqlite';
		
		// SQLite3 instance
		protected ?SQLite3 $sqlite3 = null;
		
		/**
		 * Validate configuration
		 * @param array $configuration Configuration data to validate
		 * 
		 * @throws DatabaseAdapterException
		 */
		protected function validateConfig(array $configuration): void {
			if(!isset($configuration['database'])) {
				throw new DatabaseAdapterException("Database configuration is missing database");
			}
		}
		
		/**
		 * Start the database-specific connection
		 * @param array $container The application container
		 * @return void
		 * 
		 * @throws RuntimeException
		 * @throws DatabaseAdapterException
		 */
		protected function wireUp(array $configuration): void {
			// require sqlite3 extension
			if(!extension_loaded('sqlite3')) {
				throw new RuntimeException("The SQLite3 extension is not loaded");
			}
			
			// pull the configuration and check if it is valid
			$this->validateConfig($configuration);
			
			// connect to the database
			$this->sqlite3 = new SQLite3(
				$configuration['database'],
				SQLITE3_OPEN_READWRITE
			);
			
			$this->sqlite3->enableExceptions(true);
		}
		
		/**
		 * Given a value, determine which SQLite3 type it is
		 * @param mixed $val Value to determine type of
		 * @return mixed
		 * 
		 * @throws QueryPreperationException
		 */
		protected function determineValueType(mixed $val): mixed {
			if(null === $val) {
				return SQLITE3_NULL;
			} elseif(is_int($val)) {
				return SQLITE3_INTEGER;
			} elseif(is_float($val)) {
				return SQLITE3_FLOAT;
			} elseif(is_string($val)) {
				return SQLITE3_TEXT;
			} else {
				throw new QueryPreperationException("Invalid value type, please encode value before inserting into database");
			}
		}
		
		/**
		 * Bind query parameters to a SQLite3 Statement object
		 * @param SQLite3Stmt &$statement
		 * @param array $params
		 * @return SQLite3Stmt
		 */
		protected function bindQueryParams(SQLite3Stmt &$statement, array $params): SQLite3Stmt {
			// bind params
			if(array_is_list($params)) {
				// consecutive array - process ? params
				$i = 1;
				
				foreach($params as $value) {
					//$statement->bindValue($i++, $value, $this->determineValueType($value));
					$statement->bindValue($i++, $value);   // ->bindValue appears to already determine type
				}
			} else {
				// assoc array - process :params
				foreach($params as $key => $value) {
					//$statement->bindValue($key, $value, $this->determineValueType($value));
					$statement->bindValue($key, $value);   // ->bindValue appears to already determine type
				}
			}
			
			return $statement;
		}
		
		/**
		 * Internal method to run a query. Returns SQLite3Result object or false on failure
		 * @param string $sql_query
		 * @param array $params
		 * @return SQLite3Result|false
		 */
		protected function runQuery(string $sql_query, array $params=[]): SQLite3Result|false {
			// prepare the query
			if(false === ($statement = $this->sqlite3->prepare($sql_query))) {
				throw new QueryPreperationException("Failed to prepare query");
			}
			
			// bind params
			if(!empty($params)) {
				$this->bindQueryParams($statement, $params);
			}
			
			// execute the query
			return $statement->execute();
		}
		
		/**
		 * Run a query. Generally used for anything besides SELECT statements. If an INSERT query, return the last insert ID, otherwise return the number of rows affected. Returns false on failure
		 * @param string $sql_query The SQL query to run. Optionally used in conjunction with $params
		 * @param array $params The parameters to bind to the query. Must use assoc array if using named placeholders (:variable) or consecutive array if using unnamed placeholders (?)
		 * @return int|false
		 * 
		 * @throws QueryPreperationException
		 * 
		 * @see SQLite3Stmt::bindValue
		 * @see https://www.php.net/manual/en/sqlite3stmt.bindvalue.php
		 * 
		 * @example $db->query("INSERT INTO `table` (`column`, `column2`) VALUES (:value, :value2)", [':value' => 'test', ':value2' => 'test2']);
		 * @example $db->query("INSERT INTO `table` (`column`, `column2`) VALUES (?, ?)", ['test', 'test2']);
		 * @example $db->query("INSERT INTO `table` (`column`) VALUES ('test')");
		 */
		public function query(string $sql_query, array $params=[]): int|false {
			// build result from query
			$result = $this->runQuery($sql_query, $params);
			
			// determine if error
			if(false === $result) {
				return false;
			}
			
			// if insert, return last insert ID
			if(preg_match("#^\s*INSERT#si", $sql_query)) {
				return $this->sqlite3->lastInsertRowID();
			}
			
			// return the result
			return $result;
		}
		
		/**
		 * Run a query and return an array of rows. Generally used for SELECT statements. Returns false on failure
		 * @param string $sql_query The SQL query to run. If used in conjunction with $params, use either named (:variable) or unnamed placeholders (?), not both
		 * @param array $params The parameters to bind to the query. See https://www.php.net/manual/en/pdo.prepare.php
		 * @param string|false $column_key The column to use as the array key for the results. If false, use an incrementing integer
		 * @return array|false
		 * 
		 * @see \Magnetar\Database\SQLite\DatabaseAdapter::query() for use of $sql_query and $params
		 */
		public function get_rows(
			string $sql_query,
			array $params=[],
			string|false $column_key=false
		): array|false {
			// execute the query
			$result = $this->runQuery($sql_query, $params);
			
			$rows = [];
			
			if($result->numColumns() && ($result->columnType(0) !== SQLITE3_NULL)) {
				while($row = $result->fetchArray(SQLITE3_ASSOC)) {
					if((false !== $column_key) && isset($row[ $column_key ])) {
						$rows[ $row[ $column_key ] ] = $row;
					} else {
						$rows[] = $row;
					}
				}
			}
			
			$result->finalize();
			
			return $rows;
		}
		
		/**
		 * Run a query and return a single row. Generally used for SELECT statements. Returns false on failure
		 * @param string $sql_query The SQL query to run
		 * @param array $params The parameters to bind to the query
		 * @return array|false
		 * 
		 * @see \Magnetar\Database\SQLite\DatabaseAdapter::query() for use of $sql_query and $params
		 */
		public function get_row(string $sql_query, array $params=[]): array|false {
			// execute the query
			$result = $this->runQuery($sql_query, $params);
			
			return $result->fetchArray(SQLITE3_ASSOC);
		}
		
		/**
		 * Run a query and return an array of a single column. Generally used for SELECT statements. Returns false on failure
		 * @param string $sql_query The SQL query to run
		 * @param array $params The parameters to bind to the query
		 * @param string|int $column_key The column to use as the array key for the results
		 * @return array|false
		 * 
		 * @see \Magnetar\Database\SQLite\DatabaseAdapter::query() for use of $sql_query and $params
		 */
		public function get_col(
			string $sql_query,
			array $params=[],
			string|int $column_key=0
		): array|false {
			// execute the query
			$result = $this->runQuery($sql_query, $params);
			
			$rows = [];
			
			while($row = $result->fetchArray(SQLITE3_BOTH)) {
				$rows[] = $row[ $column_key ] ?: array_shift($row);
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
		 * @see \Magnetar\Database\SQLite\DatabaseAdapter::query() for use of $sql_query and $params
		 */
		public function get_col_assoc(
			string $sql_query,
			array $params=[],
			string|int $assoc_key=0,
			string|int $column_key=1
		): array|false {
			// execute the query
			$result = $this->runQuery($sql_query, $params);
			
			$rows = [];
			
			$assoc_col = null;
			$value_col = null;
			
			while($row = $result->fetchArray(SQLITE3_ASSOC)) {
				// determine key column
				if(null === $assoc_col) {
					if(isset($row[ $assoc_key ]) && ($assoc_key !== $column_key)) {
						$assoc_col = $assoc_key;
					} else {
						$assoc_col = false;
					}
				}
				
				// determine value column
				if(null === $value_col) {
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
			
			return $rows;
		}
		
		/**
		 * Run a query and return a single value. Generally used for SELECT statements. Returns false on failure
		 * @param string $sql_query The SQL query to run
		 * @param array $params The parameters to bind to the query
		 * @param string|false $column_key The column to use as the array key for the results. False uses the first column specified in the query
		 * @return string|int|false
		 * 
		 * @see \Magnetar\Database\SQLite\DatabaseAdapter::query() for use of $sql_query and $params
		 */
		public function get_var(string $sql_query, array $params=[], string|int|false $column_key=false): string|int|false {
			// execute the query
			$result = $this->runQuery($sql_query, $params);
			
			$row = $result->fetchArray(SQLITE3_ASSOC);
			
			if(false !== $column_key) {
				return $row[ $column_key ] ?? false;
			}
			
			return array_shift($row);
		}
	}