<?php
	declare(strict_types=1);
	
	namespace Magnetar\Helpers\Facades;
	
	use Magnetar\Helpers\Facades\Facade;
	
	/**
	 * @method connection(string|null $driver_name=null): Magnetar\Database\DatabaseAdapter
	 * @method query(array $sql_query, array $params=[]): int|false;
	 * @method get_rows(string $sql_query, array $params=[], array|false $column_key=false): array|false;
	 * @method get_row(string $sql_query, array $params=[]): array|false;
	 * @method get_col(string $sql_query, array $params=[], string|int $column_key=0): array|false;
	 * @method get_col_assoc(string $sql_query, array $params=[], string $assoc_key, string|int $column_key=0): array|false;
	 * @method get_var(string $sql_query, array $params=[], string|int|false $column_key=false): string|int|false;
	 * 
	 * @see Magnetar\Database\ConnectionManager
	 */
	class DB extends Facade {
		/**
		 * Get the named key that this facade represents
		 * @return string
		 */
		protected static function getFacadeKey(): string {
			return 'database';
		}
	}