<?php
	declare(strict_types=1);
	
	namespace Magnetar\Database;
	
	interface DatabaseAdapterInterface {
		/**
		 * Run a standard query. Returns the last inserted ID if an INSERT query is used, the number of affected rows, or false on error
		 * @param string $sql_query
		 * @param array $params
		 * @return int|false
		 */
		public function query(string $sql_query, array $params=[]): int|false;
	}