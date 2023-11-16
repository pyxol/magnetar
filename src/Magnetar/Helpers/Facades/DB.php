<?php
	declare(strict_types=1);
	
	namespace Magnetar\Helpers\Facades;
	
	use Magnetar\Helpers\Facades\Facade;
	
	/**
	 * @method static \Magnetar\Database\DatabaseAdapter connection(?string $connection_name)
	 * @method static ?string getDefaultConnectionName()
	 * @method static array getConnected()
	 * @method static \Magnetar\Database\DatabaseAdapter adapter(string $connection_name)
	 * @method static string getAdapterName()
	 * @method static int|false query(string $sql_query, array $params)
	 * @method static array|false get_rows(string $sql_query, array $params, string|false $column_key)
	 * @method static array|false get_row(string $sql_query, array $params)
	 * @method static array|false get_col(string $sql_query, array $params, string|int $column_key)
	 * @method static array|false get_col_assoc(string $sql_query, array $params, string|int $assoc_key, string|int $column_key)
	 * @method static string|int|false get_var(string $sql_query, array $params, string|int|false $column_key)
	 * @method static \Magnetar\Database\QueryBuilder table(string $table_name)
	 * 
	 * @see \Magnetar\Database\ConnectionManager
	 * @see \Magnetar\Database\DatabaseAdapter
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