<?php
	declare(strict_types=1);
	
	namespace Magnetar\Database\MariaDB;
	
	use Magnetar\Database\MariaDB\DatabaseAdapter;
	use Magnetar\Database\MySQL\SelectQueryBuilder as MySQLSelectQueryBuilder;
	
	/**
	 * MariaDB select query builder
	 * 
	 * @TODO Add support for joins
	 * @TODO Add support for group by
	 * @TODO Add support for having
	 */
	class SelectQueryBuilder extends MySQLSelectQueryBuilder {
		/**
		 * Constructor
		 * @param DatabaseAdapter $db Database adapter
		 * @param string $table_name Table name. Invalid names will throw an exception
		 * 
		 * @throws QueryBuilderException
		 */
		public function __construct(
			protected DatabaseAdapter $db,
			string $table_name
		) {
			$this->table($table_name);
		}
	}