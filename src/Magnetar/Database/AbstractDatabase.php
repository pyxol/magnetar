<?php
	declare(strict_types=1);
	
	namespace Magnetar\Database;
	
	use \Exception;
	
	use Magnetar\Database\DatabaseInterface;
	use Magnetar\Config\Config;
	
	abstract class AbstractDatabase implements DatabaseInterface {
		use DatabaseTrait;
		protected Config $config;
		
		/**
		 * Connect to a MariaDB database
		 * @param string $host Hostname of the database server
		 * @param string $db_name Name of the database to connect to
		 * @param string $user Username to connect with
		 * @param string $password Password to connect with
		 * @param int|string $port Optional. Port to connect to. Defaults to 3306
		 * @throws Exception
		 */
		public function __construct(Config $config) {
			// record connection details
			$this->config = $config;
			
			// wire up to DB instance
			$this->wireUp();
		}
		
		// query
		abstract public function query(array $sql_query, array $params=[]): int|false;
		
		// get rows
		abstract public function get_rows(string $sql_query, array $params=[], array|false $column_key=false): array|false;
		
		// get row
		abstract public function get_row(string $sql_query, array $params=[]): array|false;
		
		// get column
		abstract public function get_col(string $sql_query, array $params=[], string|int $column_key=0): array|false;
		
		// get assoc column
		abstract public function get_col_assoc(string $sql_query, array $params=[], string $assoc_key, string|int $column_key=0): array|false;
		
		// get var
		abstract public function get_var(string $sql_query, array $params=[], string|int|false $column_key=false): string|int|false;
	}