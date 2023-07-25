<?php
	declare(strict_types=1);
	
	namespace Magnetar\Database\MariaDB;
	
	use Magnetar\Database\MySQL\DatabaseAdapter as MySQLDatabaseAdapter;
	
	class DatabaseAdapter extends MySQLDatabaseAdapter {
		protected string $adapter_name = 'mariadb';
	}