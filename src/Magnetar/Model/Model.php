<?php
	declare(strict_types=1);
	
	namespace Magnetar\Model;
	
	use ArrayAccess;
	
	use Magnetar\Model\HasDirtyTrait;
	use Magnetar\Model\HasLookupTrait;
	use Magnetar\Model\HasMutableTrait;
	
	/**
	 * Model class for interacting with the database.
	 * 
	 * Functions that start with an underscore are for internal use and should not be called directly.
	 */
	class Model implements ArrayAccess {
		use HasDirtyTrait,
			HasLookupTrait,
			HasMutableTrait;
		
		/**
		 * The model's data array
		 * @var array|null
		 */
		protected array|null $_data = null;
		
		/**
		 * The database connection name to use for the model. If null, the default connection is used
		 * @var string|null
		 */
		protected string|null $connection_name = null;
		
		/**
		 * Table name for the model
		 * @var string|null
		 */
		protected string|null $table = null;
		
		/**
		 * The identifier column for the model
		 * @var string
		 */
		protected string $identifier = 'id';
		
		/**
		 * AbstractObject constructor
		 * @param int|null $id The ID of the object to pull
		 */
		public function __construct(
			int|null $id=null
		) {
			// determine table (if not set)
			$this->_determineModelTable();
			
			if(null !== $id) {
				// pull model data
				$this->find($id);
			}
		}
		
		/**
		 * Determine the table name for the model by using the class name. CamelCase -> snake_case
		 * @return void
		 */
		protected function _determineModelTable(): void {
			// if table is already set, return
			if(null !== $this->table) {
				return;
			}
			
			// get the class name
			$class = get_class($this);
			
			// get the table name
			$parts = explode('\\', $class);
			$class_name = end($parts);
			
			// convert to snake_case
			$this->table = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $class_name));
		}
		
		/**
		 * Check if an offset exists
		 * @param mixed $offset The offset to check
		 * @return bool True if the offset exists, false otherwise
		 */
		public function offsetExists(mixed $offset): bool {
			return isset($this->_data[ $offset ]);
		}
		
		/**
		 * Get an offset
		 * @param mixed $offset The offset to get
		 * @return mixed The offset value
		 */
		public function offsetGet(mixed $offset): mixed {
			return $this->_data[ $offset ] ?? null;
		}
		
		/**
		 * Set an offset
		 * @param mixed $offset The offset to set
		 * @param mixed $value The value to set
		 * @return void
		 */
		public function offsetSet(mixed $offset, mixed $value): void {
			$this->_data[ $offset ] = $value;
		}
		
		/**
		 * Unset an offset
		 * @param mixed $offset The offset to unset
		 * @return void
		 */
		public function offsetUnset(mixed $offset): void {
			//unset($this->_data[ $offset ]);
			
			$this->_data[ $offset ] = null;
			
			$this->setDirtyAttribute($offset, null);
		}
		
		/**
		 * Get a property value
		 * @param string $key The property to get
		 * @return mixed The property value
		 */
		public function __get(string $key): mixed {
			return $this->_data[ $key ] ?? null;
		}
		
		/**
		 * Set a property
		 * @param string $key The property to set
		 * @param mixed $value The value to set
		 * @return void
		 */
		public function __set(string $key, mixed $value): void {
			$this->_data[ $key ] = $value;
			
			$this->setDirtyAttribute($key, $value);
		}
		
		/**
		 * Check if a property is set
		 * @param string $key The property to check
		 * @return bool True if the property is set, false otherwise
		 */
		public function __isset(string $key): bool {
			return isset($this->_data[ $key ]);
		}
	}