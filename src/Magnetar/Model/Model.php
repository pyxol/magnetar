<?php
	declare(strict_types=1);
	
	namespace Magnetar\Model;
	
	use ArrayAccess;
	
	use Magnetar\Model\HasDirtyTrait;
	use Magnetar\Model\HasLookupTrait;
	use Magnetar\Model\HasMutableTrait;
	use Magnetar\Model\HasEvents;
	use Magnetar\Model\HasComparableTrait;
	use Magnetar\Utilities\Str;
	use Magnetar\Utilities\Internals;
	
	/**
	 * Model class for interacting with the database.
	 * 
	 * Functions that start with an underscore are for internal use and should not be called directly.
	 */
	class Model implements ArrayAccess {
		use HasDirtyTrait,
			HasLookupTrait,
			HasMutableTrait,
			HasEvents,
			HasComparableTrait;
		
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
		 * Constructor
		 * @param array|null $data The ID of the model to pull
		 */
		public function __construct(
			array|null $data=null
		) {
			// determine table (if not set)
			$this->_determineModelTable();
			
			if(is_array($data)) {
				// prefill model data
				$this->_data = $data;
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
			
			// if not set already, get the class name and convert it to snake case
			$this->table = Str::snake_case(Internals::class_basename_instance($this));
		}
		
		/**
		 * Get the model's database table name
		 * @return string
		 */
		public function getTable(): string {
			return $this->table;
		}
		
		/**
		 * Get the model's identifier column name
		 * @return string
		 */
		public function getIdentifier(): string {
			return $this->identifier;
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
		
		/**
		 * Handle dynamic static method calls
		 * @param mixed $method The method to call
		 * @param mixed $arguments The arguments to pass to the method
		 * @return mixed
		 */
		public static function __callStatic(mixed $method, mixed $arguments): mixed {
			return (new static)->$method(...$arguments);
		}
	}