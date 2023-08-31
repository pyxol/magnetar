<?php
	declare(strict_types=1);
	
	namespace Magnetar\Database;
	
	use Magnetar\Database\QueryBuilder as QueryBuilder;
	
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