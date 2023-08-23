<?php
	declare(strict_types=1);
	
	namespace Magnetar\Database\MariaDB;
	
	use Magnetar\Database\MySQL\SelectQueryBuilder as MySQLSelectQueryBuilder;
	use Magnetar\Database\AbstractDatabaseAdapter;
	
	/**
	 * MariaDB select query builder
	 * 
	 * @TODO Add support for joins
	 * @TODO Add support for group by
	 * @TODO Add support for having
	 * 
	 * @TODO split off into a separate QueryBuilder, implement fetch() and fetchOne() in adapter-specific classes
	 */
	class SelectQueryBuilder extends MySQLSelectQueryBuilder {
		/**
		 * Constructor
		 * @param AbstractDatabaseAdapter $db Database adapter
		 * @param string $table_name Table name. Invalid names will throw an exception
		 * 
		 * @throws QueryBuilderException
		 */
		public function __construct(
			protected AbstractDatabaseAdapter $db,
			string $table_name
		) {
			$this->table($table_name);
		}
	}