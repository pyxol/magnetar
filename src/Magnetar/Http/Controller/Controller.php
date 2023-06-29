<?php
	declare(strict_types=1);
	
	namespace Magnetar\Http\Controller;
	
	use Magnetar\App;
	use Magnetar\Config\DatabaseConfig;
	use Magnetar\Database\MariaDB\Database;
	
	class Controller {
		protected App $app;
		protected ?Database $database = null;
		
		public function __construct(App $app) {
			// assign app
			$this->app = $app;
		}
		
		/**
		 * Get the database connection
		 * @return Database
		 */
		protected function db(): Database {
			// get the database connection
			if(is_null($this->database)) {
				$this->database = new Database(
					new DatabaseConfig()
				);
			}
			
			return $this->database;
		}
	}