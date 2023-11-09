<?php
	declare(strict_types=1);
	
	namespace Magnetar\Helpers\Facades;
	
	use Magnetar\Helpers\Facades\Facade;
	
	/**
	 * @method connection(?string $connection_name=null): Magnetar\Database\DatabaseAdapter
	 * @method getDefaultConnectionName(): ?string
	 * @method getConnected(): array
	 * @method adapter(string $connection_name): Magnetar\Database\DatabaseAdapter
	 * @method getAdapterName(): string
	 * @method query(string $sql_query, array $params=[]): int|false
	 * @method get_rows(string $sql_query, array $params=[], string|false $column_key=false): array|false
	 * @method get_row(string $sql_query, array $params=[]): array|false
	 * @method get_col(string $sql_query, array $params=[], string|int $column_key=0): array|false
	 * @method get_col_assoc(string $sql_query, array $params=[], string|int $assoc_key=0, string|int $column_key=1): array|false
	 * @method get_var(string $sql_query, array $params=[], string|int|false $column_key=false): string|int|false
	 * @method table(string $table_name): Magnetar\Database\QueryBuilder
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