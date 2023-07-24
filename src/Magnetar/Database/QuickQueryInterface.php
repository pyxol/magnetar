<?php
	declare(strict_types=1);
	
	namespace Magnetar\Database;
	
	interface QuickQueryInterface {
		// get rows
		public function get_rows(string $sql_query, array $params=[], string|false $column_key=false): array|false;
		
		// get row
		public function get_row(string $sql_query, array $params=[]): array|false;
		
		// get column
		public function get_col(string $sql_query, array $params=[], string|int $column_key=0): array|false;
		
		// get assoc column
		public function get_col_assoc(string $sql_query, array $params=[], string|int $assoc_key=0, string|int $column_key=1): array|false;
		
		// get var
		public function get_var(string $sql_query, array $params=[], string|int|false $column_key=false): string|int|false;
	}