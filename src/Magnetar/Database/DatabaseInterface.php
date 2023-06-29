<?php
	declare(strict_types=1);
	
	namespace Magnetar\Database;
	
	interface DatabaseInterface {
		// query
		public function query(array $sql_query, array $params=[]): int|false;
		
		// get rows
		public function get_rows(string $sql_query, array $params=[], array|false $column_key=false): array|false;
		
		// get row
		public function get_row(string $sql_query, array $params=[]): array|false;
		
		// get column
		public function get_col(string $sql_query, array $params=[], string|int $column_key=0): array|false;
		
		// get assoc column
		public function get_col_assoc(string $sql_query, array $params=[], string $assoc_key, string|int $column_key=0): array|false;
		
		// get var
		public function get_var(string $sql_query, array $params=[], string|int|false $column_key=false): string|int|false;
	}