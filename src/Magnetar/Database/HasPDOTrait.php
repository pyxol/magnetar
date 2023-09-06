<?php
	declare(strict_types=1);
	
	namespace Magnetar\Database;
	
	use PDO;
	use PDOStatement;
	
	use Magnetar\Database\Exceptions\DatabaseAdapterException;
	
	/**
	 * Provides the PDO handler instance and methods to bind params to a PDOStatement
	 */
	trait HasPDOTrait {
		/**
		 * PDO handler instance
		 * @var PDO
		 */
		protected PDO $dbh;
		
		/**
		 * The DSN string to use when creating the PDO instance
		 */
		protected ?string $dsn = null;
		
		/**
		 * Get an array of options to use when creating the PDO instance
		 * @return array
		 */
		protected function PDOOptions(): array {
			// individual adapters can override this method to manipulate these default PDO options
			return [
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			];
		}
		
		/**
		 * Create the connection to the database
		 * @return void
		 */
		protected function createConnection(): void {
			// create the PDO instance
			$this->dbh = new PDO(
				$this->dsn ??= $this->generateDSN(),
				$this->connection_config['user'] ?? null,
				$this->connection_config['password'] ?? null,
				$this->PDOOptions()
			);
		}
		
		/**
		 * Generate the DSN string. Individual adapters must override this method
		 * @return string
		 */
		protected function generateDSN(): string {
			// individual adapters must override this method
			throw new DatabaseAdapterException("Database adapter needs to override the generateDSN method.");
		}
		
		/**
		 * Bind parameters to a PDOStatement
		 * @param PDOStatement $statement The PDOStatement to bind the parameters to
		 * @param array $params The parameters to bind to the statement. Either an assoc array or a consecutive array
		 * @return void
		 */
		protected function bindStatementParams(
			PDOStatement $statement,
			array $params,
			bool $prepend_param_key_with_colon=false
		): PDOStatement {
			$is_list = array_is_list($params);
			
			foreach($params as $param_key => $param_value) {
				if($is_list) {
					$param_key++;   // increment the key by 1 to match the PDOStatement bindValue() method which starts at 1
				} else {
					if($prepend_param_key_with_colon) {
						// prepend the param key with a colon
						$param_key = ":". $param_key;
					}
				}
				
				$statement->bindValue(
					$param_key,
					$param_value,
					match(gettype($param_value)) {
						'boolean' => PDO::PARAM_BOOL,
						'integer' => PDO::PARAM_INT,
						'double' => PDO::PARAM_STR,
						'string' => PDO::PARAM_STR,
						'array' => PDO::PARAM_STR,
						'object' => PDO::PARAM_STR,
						'resource' => PDO::PARAM_STR,
						'resource (closed)' => PDO::PARAM_STR,
						'NULL' => PDO::PARAM_NULL,
						'unknown type' => PDO::PARAM_STR,
					}
				);
			}
			
			return $statement;
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
				$statement = $this->dbh->prepare($sql_query);
				
				// bind any params to the statement
				if(!empty($params)) {
					$statement = $this->bindStatementParams($statement, $params);
				}
				
				// execute the query
				$result = $statement->execute();
			} else {
				// execute the query
				$result = $this->dbh->exec($sql_query);
			}
			
			if(preg_match("#^\s*INSERT#si", $sql_query)) {
				return $this->dbh->lastInsertId();
			}
			
			// return the result
			return $result;
		}
	}