<?php
	declare(strict_types=1);
	
	namespace Magnetar\Database;
	
	interface DatabaseInterface {
		public function query(array $sql_query, array $params=[]): int|false;
		
		
	}