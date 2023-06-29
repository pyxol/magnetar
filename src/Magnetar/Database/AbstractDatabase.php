<?php
	declare(strict_types=1);
	
	namespace Magnetar\Database;
	
	use \Exception;
	
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
	}