<?php
	declare(strict_types=1);
	
	namespace Magnetar\Database\MariaDB;
	
	use PDO;
	use RuntimeException;
	
	use Magnetar\Container\Container;
	use Magnetar\Database\AbstractDatabaseAdapter;
	use Magnetar\Database\QuickQueryInterface;
	use Magnetar\Database\DatabaseAdapterException;
	
	class DatabaseAdapter extends AbstractDatabaseAdapter implements QuickQueryInterface {
		// PDO instance
		protected PDO|null $pdo = null;
		
		/**
		 * Start the database-specific connection
		 * @param Container $container The application container
		 * @return void
		 * 
		 * @throws RuntimeException
		 * @throws DatabaseAdapterException
		 */
		protected function wireUp(Container $container): void {
			if(!extension_loaded('pdo_mysql')) {
				throw new RuntimeException("The PDO MySQL extension is not loaded");
			}
			
			// pull the configuration and check if it is valid
			$this->throwIfInvalidConfig(
				$config = $container['config']->get('database.connections.mariadb', [])
			);
			
			// PDO options
			$default_options = [
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			];
			
			// connect to the database
			$this->pdo = new PDO("mysql:host=". $config['host'] .";dbname=". $config['database'], $config['user'], $config['password'], $default_options);
			
			// optional charset settings
			if(isset($config['charset'])) {
				$this->pdo->exec("SET NAMES ". $config['charset']);
				$this->pdo->exec("SET CHARACTER SET ". $config['charset']);
			}
		}
		
		/**
		 * Run a query. Generally used for anything besides SELECT statements. If an INSERT query, return the last insert ID, otherwise return the number of rows affected. Returns false on failure
		 * @param string $sql_query The SQL query to run. If used in conjunction with $params, use either named (:variable) or unnamed placeholders (?), not both
		 * @param array $params The parameters to bind to the query. See https://www.php.net/manual/en/pdo.prepare.php
		 * @return int|false
		 * 
		 * @see https://www.php.net/manual/en/pdo.exec.php
		 * @see https://www.php.net/manual/en/pdo.prepare.php
		 * 
		 * @example $db->query("INSERT INTO `table` (`column`) VALUES (:value)", [':value' => 'test']);
		 * @example $db->query("INSERT INTO `table` (`column`) VALUES (?)", ['test']);
		 * @example $db->query("INSERT INTO `table` (`column`) VALUES ('test')");
		 */
		public function query(array $sql_query, array $params=[]): int|false {
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
		 * @param string $sql_query The SQL query to run. If used in conjunction with $params, use either named (:variable) or unnamed placeholders (?), not both
		 * @param array $params The parameters to bind to the query. See https://www.php.net/manual/en/pdo.prepare.php
		 * @param string|false $column_key The column to use as the array key for the results. If false, use an incrementing integer
		 * @return array|false
		 * 
		 * @see https://www.php.net/manual/en/pdo.query.php
		 * @see https://www.php.net/manual/en/pdo.prepare.php
		 * 
		 * @example $db->select("SELECT * FROM `table` WHERE `column` = :value", [':value' => 'test']);
		 * @example $db->select("SELECT * FROM `table` WHERE `column` = ?", ['test']);
		 * @example $db->select("SELECT * FROM `table` WHERE `column` = 'test'");
		 */
		public function get_rows(string $sql_query, array $params=[], array|false $column_key=false): array|false {
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
		 * @param string $sql_query The SQL query to run. If used in conjunction with $params, use either named (:variable) or unnamed placeholders (?), not both
		 * @param array $params The parameters to bind to the query. See https://www.php.net/manual/en/pdo.prepare.php
		 * @return array|false
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
		 * @param string $sql_query The SQL query to run. If used in conjunction with $params, use either named (:variable) or unnamed placeholders (?), not both
		 * @param array $params The parameters to bind to the query. See https://www.php.net/manual/en/pdo.prepare.php
		 * @param string|int $column_key The column to use as the array key for the results
		 * @return array|false
		 */
		public function get_col(string $sql_query, array $params=[], string|int $column_key=0): array|false {
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
		 * @param string $sql_query The SQL query to run. If used in conjunction with $params, use either named (:variable) or unnamed placeholders (?), not both
		 * @param array $params The parameters to bind to the query. See https://www.php.net/manual/en/pdo.prepare.php
		 * @param string|int $assoc_key The column to use as the array key for the results. Defaults to first column
		 * @param string|int $column_key The column to use as the array value for the results. Defaults to second column
		 * @return array|false
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
		 * @param string $sql_query The SQL query to run. If used in conjunction with $params, use either named (:variable) or unnamed placeholders (?), not both
		 * @param array $params The parameters to bind to the query. See https://www.php.net/manual/en/pdo.prepare.php
		 * @param string|false $column_key The column to use as the array key for the results. False uses the first column specified in the query
		 * @return string|int|false
		 */
		public function get_var(string $sql_query, array $params=[], string|int|false $column_key=false): string|int|false {
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