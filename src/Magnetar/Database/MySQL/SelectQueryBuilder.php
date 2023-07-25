<?php
	declare(strict_types=1);
	
	namespace Magnetar\Database\MySQL;
	
	use Magnetar\Database\MySQL\DatabaseAdapter;
	use Magnetar\Database\QueryBuilderException;
	
	/**
	 * MySQL select query builder
	 * 
	 * @TODO Add support for joins
	 * @TODO Add support for group by
	 * @TODO Add support for having
	 * @TODO Add support for order by
	 */
	class SelectQueryBuilder {
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
		 * Order by
		 * @var string
		 */
		protected string $orderBy = '';
		
		/**
		 * Order
		 * @var string
		 */
		protected string $order = 'ASC';
		
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
		
		/**
		 * Set the table name
		 * @param string $table_name
		 * @return self
		 */
		public function table(string $table_name): self {
			// validate table name
			if(empty($table_name)) {
				throw new QueryBuilderException("Table name is empty");
			} elseif(!is_string($table_name) || !preg_match("#^[a-z0-9_]+$#i", $table_name)) {
				throw new QueryBuilderException("Table name has invalid characters");
			}
			
			$this->table_name = $table_name;
			
			return $this;
		}
		
		/**
		 * Add field(s) to select. If no fields are provided, all fields will be selected
		 * @param array|string|null $field_names
		 * @return self
		 */
		public function select(array|string|null $field_names=null): self {
			if(null === $field_names) {
				$this->fields[] = "*";
				
				return $this;
			}
			
			// convert comma separated field list string to array
			if(!is_array($field_names)) {
				$field_names = explode(',', $field_names);
			}
			
			foreach($field_names as $field_name) {
				$field_name = trim($field_name, " ,`");
				
				if('' !== $field_name) {
					$this->fields[] = "`". $field_name ."`";
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
				throw new QueryBuilderException("Field for selectRaw is empty");
			}
			
			if(!is_null($as) && ($as !== $field)) {
				$this->fields[] = $field ." as `". $as ."`";
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
			mixed $value=null,
			string $comparison_operator='=',
			bool $use_raw_value=false
		): self {
			// sanitize comparison operator
			$comparison_operator = strtoupper(trim($comparison_operator));
			
			// null value
			if(null === $value) {
				if(in_array($comparison_operator, ['!=', 'NOT LIKE', 'NOT IN', 'IS NOT NULL'])) {
					$this->wheres[] = "`". $column_name ."` IS NOT NULL";
				} else {
					$this->wheres[] = "`". $column_name ."` IS NULL";
				}
				
				return $this;
			}
			
			// validate comparison operator
			if(!in_array($comparison_operator, ['=', '!=', '<', '>', '<=', '>=', 'LIKE', 'NOT LIKE', 'IN', 'NOT IN'])) {
				throw new QueryBuilderException("Invalid comparison operator");
			}
			
			// validate value
			if(is_object($value)) {
				throw new QueryBuilderException("Invalid value type, must be scalar");
			}
			
			// if using raw value, do not set value to be escaped
			if($use_raw_value) {
				$this->wheres[] = "`". $column_name ."` ". $comparison_operator ." ". $value;
				
				return $this;
			}
			
			// handle certain comparison operators
			if(in_array($comparison_operator, ['IN', 'NOT IN'])) {
				// value should be an array
				if(!is_array($value)) {
					throw new QueryBuilderException("Invalid value type for specified comparison operator, value must be an array");
				}
				
				// record each value
				$in_vals = [];
				
				foreach($value as $val) {
					$in_vals[] = "?";
					
					$this->whereParams[] = $val;
				}
				
				$this->wheres[] = "`". $column_name ."` ". $comparison_operator ." (". implode(', ', $in_vals) .")";
				
				return $this;
			}
			
			if(is_array($value)) {
				throw new QueryBuilderException("Invalid value type, raw arrays cannot be used");
			}
			
			$this->wheres[] = "`". $column_name ."` ". $comparison_operator ." ?";
			
			return $this;
		}
		
		/**
		 * Accept a raw where clause. Using where() is preferred
		 * @param string $where_clause Raw partial where statement. Effectively a single [...] of "WHERE [...] AND [...]"
		 * @return self
		 */
		public function whereRaw(string $where_clause): self {
			$this->wheres[] = $where_clause;
			
			return $this;
		}
		
		/**
		 * Set the limit
		 * @param int $limit
		 * @return self
		 */
		public function limit(int $limit): self {
			$this->limit = $limit;
			
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
				throw new QueryBuilderException("Table name is empty");
			}
			
			// start query
			$query = "SELECT";
			
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
				
				foreach($this->wheres as $where) {
					$query .= ' '. $where;
				}
				
				// attach any where-specific params
				if(!empty($this->whereParams)) {
					$params = array_merge($params, $this->whereParams);
				}
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
			list($query, $params) = $this->buildQueryAndParams();
			
			// reset query builder (to prevent accidental reuse)
			$this->reset();
			
			// get all rows
			return $this->db->get_rows($query, $params);
		}
		
		/**
		 * Build the query and fetch a single row
		 * @return array
		 */
		public function fetchOne(): array {
			list($query, $params) = $this->buildQueryAndParams();
			
			// reset query builder (to prevent accidental reuse)
			$this->reset();
			
			// get a single row
			return $this->db->get_row($query, $params);
		}
		
		/**
		 * @TMP Debug function
		 * @return string
		 */
		public function debugQueryParams(): string {
			return print_r($this->buildQueryAndParams(), true);
		}
	}