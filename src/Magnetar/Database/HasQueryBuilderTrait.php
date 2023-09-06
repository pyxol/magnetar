<?php
	declare(strict_types=1);
	
	namespace Magnetar\Database;
	
	use Magnetar\Database\QueryBuilder as QueryBuilder;
	
	/**
	 * Provides the table method to start a query builder instance
	 */
	trait HasQueryBuilderTrait {
		/**
		 * Start a SELECT query builder instance
		 * @param string $table_name
		 * @return SelectQueryBuilder
		 */
		public function table(string $table_name): QueryBuilder {
			return new QueryBuilder($this, $table_name);
		}
	}