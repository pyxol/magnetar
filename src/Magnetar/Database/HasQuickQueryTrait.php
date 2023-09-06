<?php
	declare(strict_types=1);
	
	namespace Magnetar\Database;
	
	use PDO;
	
	/**
	 * Provides quick query methods for a database adapter
	 * 
	 * @todo replace PDO-specific code with a more generic solution
	 * @todo bindStatementParams is in HasPDOTrait
	 */
	trait HasQuickQueryTrait {
		/**
		 * Run a query and return an array of rows. Generally used for SELECT statements. Returns false on failure
		 * @param string $sql_query The SQL query to run
		 * @param array $params The parameters to bind to the query
		 * @param string|false $column_key The column to use as the array key for the results. If false, use an incrementing integer
		 * @return array|false
		 */
		public function get_rows(
			string $sql_query,
			array $params=[],
			string|false $column_key=false
		): array|false {
			// prepare the query
			$statement = $this->dbh->prepare($sql_query);
			
			// bind any params to the statement
			if(!empty($params)) {
				$statement = $this->bindStatementParams($statement, $params);
			}
			
			// execute the query
			if(false === $statement->execute()) {
				// @TODO log error
				
				return false;
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
		 * @see \Magnetar\Database\MySQL::query() for $params usage with $sql_query
		 */
		public function get_row(string $sql_query, array $params=[]): array|false {
			// prepare the query
			$statement = $this->dbh->prepare($sql_query);
			
			// bind any params to the statement
			if(!empty($params)) {
				$statement = $this->bindStatementParams($statement, $params);
			}
			
			// execute the query
			if(false === $statement->execute()) {
				return false;
			}
			
			if(!$statement->rowCount()) {
				// @TODO log error
				
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
		 * @see \Magnetar\Database\MySQL::query() for $params usage with $sql_query
		 */
		public function get_col(
			string $sql_query,
			array $params=[],
			string|int $column_key=0
		): array|false {
			// prepare the query
			$statement = $this->dbh->prepare($sql_query);
			
			// bind any params to the statement
			if(!empty($params)) {
				$statement = $this->bindStatementParams($statement, $params);
			}
			
			// execute the query
			if(false === $statement->execute()) {
				// @TODO log error
				
				return false;
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
		 * @see \Magnetar\Database\MySQL::query() for $params usage with $sql_query
		 */
		public function get_col_assoc(
			string $sql_query,
			array $params=[],
			string|int $assoc_key=0,
			string|int $column_key=1
		): array|false {
			// prepare the query
			$statement = $this->dbh->prepare($sql_query);
			
			// bind any params to the statement
			if(!empty($params)) {
				$statement = $this->bindStatementParams($statement, $params);
			}
			
			// execute the query
			if(false === $statement->execute()) {
				// @TODO log error
				
				return false;
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
		 * @see \Magnetar\Database\MySQL::query() for $params usage with $sql_query
		 */
		public function get_var(
			string $sql_query,
			array $params=[],
			string|int|false $column_key=false
		): string|int|false {
			// prepare the query
			if(false === ($row = $this->get_row($sql_query, $params))) {
				return false;
			}
			
			if(false !== $column_key) {
				if(isset($row[ $column_key ])) {
					return $row[ $column_key ];
				}
				
				return false;
			}
			
			return array_shift($row);
		}
	}