<?php
	declare(strict_types=1);
	
	namespace Magnetar\Database;
	
	use Magnetar\Database\AbstractDatabase;
	
	interface DatabaseAdapterInterface extends AbstractDatabaseAdapter {
		// query
		public function query(array $sql_query, array $params=[]): int|false;
	}