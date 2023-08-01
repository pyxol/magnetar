<?php
	declare(strict_types=1);
	
	namespace Magnetar\Database\MariaDB;
	
	use Magnetar\Database\MySQL\DatabaseAdapter as MySQLDatabaseAdapter;
	use Magnetar\Database\MariaDB\SelectQueryBuilder;
	
	class DatabaseAdapter extends MySQLDatabaseAdapter {
		protected string $adapter_name = 'mariadb';
		
		/**
		 * Start a SELECT query builder instance
		 * @param string $table_name
		 * @return SelectQueryBuilder
		 */
		public function table(string $table_name): SelectQueryBuilder {
			return new SelectQueryBuilder($this, $table_name);
		}
	}