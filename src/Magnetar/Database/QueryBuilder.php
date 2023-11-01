<?php
	declare(strict_types=1);
	
	namespace Magnetar\Database;
	
	use Magnetar\Database\DatabaseAdapter;
	use Magnetar\Database\Exceptions\QueryBuilderException;
	
	/**
	 * Query Builder
	 * 
	 * Implies MySQL-like SQL syntax, extend this class to implement other syntaxes.
	 * 
	 * @todo Add support for joins
	 * @todo Add support for group by
	 * @todo Add support for having
	 */
	class QueryBuilder {
		/**
		 * Table name
		 * @var string
		 */
		protected ?string $table_name = null;
		
		/**
		 * Fields to select
		 * @var array
		 */
		protected array $fields = [];
		
		/**
		 * Where clauses
		 * @var array
		 */
		protected array $wheres = [];
		
		/**
		 * Parameters for where clauses
		 * @var array
		 */
		protected array $whereParams = [];
		
		/**
		 * Order by. Array of strings, like: ['`field1` ASC', '`field2` DESC']
		 * @var array
		 */
		protected array $orders = [];
		
		/**
		 * Limit
		 * @var int
		 */
		protected int $limit = 0;
		
		/**
		 * Offset
		 * @var int
		 */
		protected int $offset = 0;
		
		/**
		 * Constructor
		 * @param DatabaseAdapter $adapter Database adapter
		 * @param string $table_name Table name. Invalid names will throw an exception
		 * 
		 * @throws QueryBuilderException
		 */
		public function __construct(
			protected DatabaseAdapter $adapter,
			string $table_name
		) {
			$this->table($table_name);
		}
		
		/**
		 * Set the table name
		 * @param string $table_name
		 * @return self
		 */
		public function table(string $table_name): self {
			// validate table name
			if(!preg_match('#^[a-z0-9_]+$#i', $table_name)) {
				throw new QueryBuilderException('Table name has invalid characters');
			}
			
			$this->table_name = $table_name;
			
			return $this;
		}
		
		/**
		 * Add field(s) to select. If no fields are provided, all fields will be selected
		 * @param array|string|null $field_names Optional. The field(s) to pull. If empty, all fields will be selected. If a string, it will be converted to an array by splitting columns by comma. Defaults to null
		 * @return self
		 */
		public function select(array|string|null $field_names=null): self {
			if(null === $field_names) {
				$this->fields[] = '*';
				
				return $this;
			}
			
			if(func_num_args() > 1) {
				// multiple arguments were passed, use them as field names
				$field_names = func_get_args();
			} elseif(!is_array($field_names)) {
				// convert comma separated field list string to array
				$field_names = explode(',', $field_names);
			}
			
			foreach($field_names as $field_name) {
				$field_name = trim($field_name, ' ,`');
				
				if('' !== $field_name) {
					$this->fields[] = '`'. $field_name .'`';
				}
			}
			
			return $this;
		}
		
		/**
		 * Add a raw field to select
		 * @param string $field The field to pull. Typically a function like COUNT(*). Do not include the AS keyword or backticks
		 * @param ?string $as Optional. The alias for the field. If empty, the field name will be used
		 * @return self
		 */
		public function selectRaw(string $field, ?string $as=null): self {
			$field = trim($field);
			
			if('' === $field) {
				throw new QueryBuilderException('Field for selectRaw is empty');
			}
			
			if(!is_null($as) && ($as !== $field)) {
				$this->fields[] = $field .' as `'. $as .'`';
			} else {
				$this->fields[] = $field;
			}
			
			return $this;
		}
		
		/**
		 * Add a where clause
		 * @param string $column_name The column name
		 * @param mixed $value The value to compare against. NULL will be converted to IS NULL/IS NOT NULL
		 * @param string $comparison_operator One of =, !=, <, >, <=, >=, LIKE, NOT LIKE, IN, NOT IN, IS NULL, IS NOT NULL
		 * @param bool $use_raw_value Set to true to use the value as-is, without escaping. Generally for using functions like NOW()
		 * @return self
		 */
		public function where(
			string $column_name,
			mixed $value_or_comparison_operator=null,
			mixed $value=null,
			bool $use_raw_value=false
		): self {
			if(func_num_args() == 2) {
				$value = $value_or_comparison_operator;
				$comparison_operator = '=';
			} else {
				// sanitize comparison operator
				$comparison_operator = strtoupper(trim($value_or_comparison_operator));
			}
			
			// null value
			if(null === $value) {
				if(in_array($comparison_operator, ['!=', 'NOT LIKE', 'NOT IN', 'IS NOT NULL'])) {
					$this->wheres[] = '`'. $column_name .'` IS NOT NULL';
				} else {
					$this->wheres[] = '`'. $column_name .'` IS NULL';
				}
				
				return $this;
			}
			
			// validate comparison operator
			if(!in_array($comparison_operator, ['=', '!=', '<', '>', '<=', '>=', 'LIKE', 'NOT LIKE', 'IN', 'NOT IN'])) {
				throw new QueryBuilderException('Invalid comparison operator');
			}
			
			// validate value
			if(is_object($value)) {
				throw new QueryBuilderException('Invalid value type, must be scalar');
			}
			
			// if using raw value, do not set value to be escaped
			if($use_raw_value) {
				$this->wheres[] = '`'. $column_name .'` '. $comparison_operator .' '. $value;
				
				return $this;
			}
			
			// handle certain comparison operators
			if(in_array($comparison_operator, ['IN', 'NOT IN'])) {
				// value should be an array
				if(!is_array($value)) {
					throw new QueryBuilderException('Invalid value type for specified comparison operator, value must be an array');
				}
				
				// record each value
				$in_vals = [];
				
				foreach($value as $val) {
					$in_vals[] = '?';
					
					$this->whereParams[] = $val;
				}
				
				$this->wheres[] = '`'. $column_name .'` '. $comparison_operator .' ('. implode(', ', $in_vals) .')';
				
				return $this;
			}
			
			if(is_array($value)) {
				throw new QueryBuilderException('Invalid value type, raw arrays cannot be used');
			}
			
			// add where clause
			$this->wheres[] = '`'. $column_name .'` '. $comparison_operator .' ?';
			
			// assign value to params
			$this->whereParams[] = $value;
			
			return $this;
		}
		
		/**
		 * Accept a raw where clause. Using where() is preferred
		 * @param string $where_clause Raw partial where statement. Effectively a single [...] of 'WHERE [...] AND [...]'
		 * @return self
		 */
		public function whereRaw(string $where_clause): self {
			$this->wheres[] = $where_clause;
			
			return $this;
		}
		
		/**
		 * Only use rows where column value is null
		 * @param string $column Column name
		 * @return self
		 */
		public function whereNull(string $column): self {
			return $this->where($column, null);
		}
		
		/**
		 * Only use rows where column value is not null
		 * @param string $column Column name
		 * @return self
		 */
		public function whereNotNull(string $column): self {
			return $this->where($column, '!=', null);
		}
		
		/**
		 * Define the order by clause
		 * @param string $column_name Column name to order by
		 * @param string $direction ASC or DESC
		 * @return self
		 */
		public function orderBy(string $column_name, string $direction='ASC'): self {
			if(!preg_match('#^(?:asc|desc)$#si', $direction)) {
				throw new QueryBuilderException('Invalid direction param in orderBy method');
			}
			
			$this->orders[] = '`'. $column_name .'` '. strtoupper($direction);
			
			return $this;
		}
		
		/**
		 * Reset the order by clause(s)
		 * @return self
		 */
		public function unsetOrderBy(): self {
			$this->orders = [];
			
			return $this;
		}
		
		/**
		 * Set the limit
		 * @param int $limit The number of rows to return
		 * @param int|null $offset Optional. The offset to start at. Defaults to null (do not alter offset)
		 * @return self
		 */
		public function limit(int $limit, int|null $offset=null): self {
			$this->limit = $limit;
			
			if(null !== $offset) {
				return $this->offset($offset);
			}
			
			return $this;
		}
		
		/**
		 * Unset the limit
		 * @return self
		 */
		public function unsetLimit(): self {
			$this->limit = 0;
			
			return $this;
		}
		
		/**
		 * Set the offset
		 * @param int $offset
		 * @return self
		 */
		public function offset(int $offset): self {
			$this->offset = $offset;
			
			return $this;
		}
		
		/**
		 * Unset the offset
		 * @return self
		 */
		public function unsetOffset(): self {
			$this->offset = 0;
			
			return $this;
		}
		
		/**
		 * Reset the query builder
		 * @return array
		 */
		protected function reset(): self {
			$this->table_name = null;
			$this->fields = [];
			$this->wheres = [];
			$this->whereParams = [];
			$this->offset = 0;
			$this->limit = 0;
			
			return $this;
		}
		
		/**
		 * Internal use only. Build the raw query and parameters
		 * @return array
		 */
		protected function buildQueryAndParams(): array {
			if(null === $this->table_name) {
				throw new QueryBuilderException('Table name is empty');
			}
			
			// start query
			$query = 'SELECT';
			
			// start the params array
			$params = [];
			
			// fields
			if(!empty($this->fields)) {
				$query .= ' '. implode(', ', $this->fields);
			} else {
				$query .= ' *';
			}
			
			// table
			$query .= ' FROM `'. $this->table_name .'`';
			
			// where
			if(!empty($this->wheres)) {
				$query .= ' WHERE';
				
				foreach($this->wheres as $i => $where) {
					$query .= (($i > 0)?' AND ':' ') . $where;
				}
				
				// attach any where-specific params
				if(!empty($this->whereParams)) {
					$params = array_merge($params, $this->whereParams);
				}
			}
			
			// order by
			if(!empty($this->orders)) {
				$query .= ' ORDER BY '. implode(', ', $this->orders);
			}
			
			// limit
			if($this->limit > 0) {
				$query .= ' LIMIT '. $this->limit;
			}
			
			// offset
			if($this->offset > 0) {
				$query .= ' OFFSET '. $this->offset;
			}
			
			return [$query, $params];
		}
		
		/**
		 * Build the query and fetch the results
		 * @return array
		 */
		public function fetch(): array {
			[$query, $params] = $this->buildQueryAndParams();
			
			// reset query builder (to prevent accidental reuse)
			$this->reset();
			
			// get all rows
			return $this->adapter->get_rows($query, $params);
		}
		
		/**
		 * Build the query and fetch a single row
		 * @return array|false
		 */
		public function fetchOne(): array|false {
			[$query, $params] = $this->buildQueryAndParams();
			
			// reset query builder (to prevent accidental reuse)
			$this->reset();
			
			// get a single row
			return $this->adapter->get_row($query, $params);
		}
		
		/**
		 * Build the query and fetch a column as a simple array
		 * @param string|int $column_key The column to use as the array key for the results. If empty, fetches the first column
		 * @return array|false
		 */
		public function fetchCol(string|int $column_key=0): array|false {
			[$query, $params] = $this->buildQueryAndParams();
			
			// reset query builder (to prevent accidental reuse)
			$this->reset();
			
			// get all rows
			return $this->adapter->get_col($query, $params, $column_key);
		}
		
		/**
		 * Build the query and fetch the value of the first row's specified column
		 * @param string|int $column_key The column to use as the array key for the results. If empty, fetches the first column
		 * @return string|int|false
		 */
		public function fetchVar(string|int $column_key=0): string|int|false {
			[$query, $params] = $this->buildQueryAndParams();
			
			// reset query builder (to prevent accidental reuse)
			$this->reset();
			
			// get all rows
			return $this->adapter->get_var($query, $params, $column_key);
		}
		
		/**
		 * Count the number of rows matching the query
		 * @return int
		 */
		public function count(): int {
			// set fields to only be COUNT(*)
			$this->fields = ['COUNT(*)'];
			
			// reset order by, limit, and offset
			$this->orders = [];
			$this->limit = 0;
			$this->offset = 0;
			
			// build query
			[$query, $params] = $this->buildQueryAndParams();
			
			// reset query builder (to prevent accidental reuse)
			$this->reset();
			
			// get all rows
			if(false === ($count = $this->adapter->get_var($query, $params, 0))) {
				return 0;
			}
			
			return $count;
		}
		
		/**
		 * Insert a row (or rows) into the database table
		 * @param array $data The data to insert. Keys should be column names, values should be the data to insert. Arrays of data will be inserted as multiple rows
		 * @param bool $ignoreDuplicate Set to true to ignore duplicate key errors (if a unique index exists) which will prevent the query from failing but will not insert the row
		 * @return int|array Returns the insert ID. If an array of insert data was provided, returns an array of insert IDs
		 * 
		 * @example $db->insert( ['column' => 'value'] );
		 * @example $db->insert( [ ['column' => 'value'], ['column' => 'value2'] ] );
		 */
		public function insert(
			array $data,
			bool $ignoreDuplicate=false
		): int {
			// insert multiple rows?
			if(array_is_list($data)) {
				// keep track of table name since it will be reset after each insert
				$table_name = $this->table_name;
				
				$insert_ids = [];
				
				foreach($data as $data_row) {
					$insert_ids[] = $this->table($table_name)->insert($data_row);
				}
				
				return $insert_ids;
			}
			
			// build query
			$query = 'INSERT '. ($ignoreDuplicate?'IGNORE ':'');
			$query .= 'INTO `'. $this->table_name .'`';
			
			// build fields
			$fields = [];
			$values = [];
			
			foreach($data as $field => $value) {
				$fields[] = '`'. $field .'`';
				$values[] = '?';
			}
			
			$query .= ' ('. implode(', ', $fields) .')';
			$query .= ' VALUES ('. implode(', ', $values) .')';
			
			// reset query builder (to prevent accidental reuse)
			$this->reset();
			
			// execute query and return last insert ID
			$insert_id = $this->adapter->query(
				$query,
				array_values($data)
			);
			
			// return insert ID
			return $insert_id;
		}
		
		/**
		 * Insert a row (or rows) into the database table, ignoring duplicate key errors (if a unique index exists) which will prevent the query from failing but will not insert the row
		 * @param array $data The data to insert. Keys should be column names, values should be the data to insert. Arrays of data will be inserted as multiple rows
		 * @return int|array Returns the insert ID. If an array of insert data was provided, returns an array of insert IDs
		 * 
		 * @example $db->insertIgnore( ['column' => 'value'] );
		 * @example $db->insertIgnore( [ ['column' => 'value'], ['column' => 'value2'] ] );
		 * 
		 * @see insert()
		 */
		public function insertIgnore(array $data): int {
			return $this->insert($data, true);
		}
		
		/**
		 * Update a row (or rows) in the database table
		 * @param array $data The data to update. Keys should be column names, values should be the data to update
		 * @param bool $allowEmptyWhere Safety check. Set to true to allow the update with no where values (will update all rows)
		 * @return void
		 * 
		 * @throws QueryBuilderException
		 * @throws DatabaseException
		 * 
		 * @example $db->update( ['column' => 'value'] );
		 */
		public function update(
			array $data,
			bool $allowEmptyWhere=false
		): void {
			// build query
			$query = 'UPDATE `'. $this->table_name .'`';
			
			// build fields
			$fields = [];
			$params = [];
			
			foreach($data as $field => $value) {
				$fields[] = '`'. $field .'` = ?';
				$params[] = $value;
			}
			
			$query .= ' SET '. implode(', ', $fields);
			
			// where
			if(!empty($this->wheres)) {
				$query .= ' WHERE';
				
				foreach($this->wheres as $i => $where) {
					$query .= (($i > 0)?' AND ':' ') . $where;
				}
				
				// attach any where-specific params
				if(!empty($this->whereParams)) {
					$params = array_merge($params, $this->whereParams);
				}
			} elseif(!$allowEmptyWhere) {
				throw new QueryBuilderException('Cannot update all rows in table without a where clause (or a bypass)');
			}
			
			// reset query builder (to prevent accidental reuse)
			$this->reset();
			
			// execute query and return number of rows affected
			$this->adapter->query(
				$query,
				$params
			);
		}
		
		
		/**
		 * Delete a row (or rows) from the database table
		 * @param bool $allowEmptyWhere Safety check. Set to true to allow deletes with no where values (allowing all rows to be deleted)
		 * @return void
		 * 
		 * @throws QueryBuilderException
		 * @throws DatabaseException
		 * 
		 * @example $db->delete();
		 */
		public function delete(
			bool $allowEmptyWhere=false
		): void {
			// build query
			$query = 'DELETE FROM `'. $this->table_name .'`';
			
			// keep track of params
			$params = [];
			
			// where
			if(!empty($this->wheres)) {
				$query .= ' WHERE';
				
				foreach($this->wheres as $i => $where) {
					$query .= (($i > 0)?' AND ':' ') . $where;
				}
				
				// attach any where-specific params
				if(!empty($this->whereParams)) {
					$params = array_merge($params, $this->whereParams);
				}
			} elseif(!$allowEmptyWhere) {
				throw new QueryBuilderException('Cannot delete all rows in table without a where clause (or a bypass)');
			}
			
			// reset query builder (to prevent accidental reuse)
			$this->reset();
			
			// execute query and return number of rows affected
			$this->adapter->query($query, $params);
		}
	}