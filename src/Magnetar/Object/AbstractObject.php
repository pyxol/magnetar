<?php
	declare(strict_types=1);
	
	namespace Magnetar\Object;
	
	use ArrayAccess;
	
	/**
	 * Abstract object class for userland objects
	 * @abstract
	 * 
	 * @todo lots of work needed here
	 * @todo rename to something that isn't so generic
	 */
	abstract class AbstractObject implements ArrayAccess {
		/**
		 * The object array
		 * @var array|null
		 */
		public ?array $object = null;
		
		/**
		 * AbstractObject constructor
		 * @param int|null $id The ID of the object to pull
		 */
		public function __construct(
			int|null $id=null
		) {
			// pull object
			$this->pullObject($id);
		}
		
		// get the object from storage
		abstract protected function pullObject(int|null $id): void;
		
		/**
		 * Check if an offset exists
		 * @param mixed $offset The offset to check
		 * @return bool True if the offset exists, false otherwise
		 */
		public function offsetExists(mixed $offset): bool {
			return isset($this->object[ $offset ]);
		}
		
		/**
		 * Get an offset
		 * @param mixed $offset The offset to get
		 * @return mixed The offset value
		 */
		public function offsetGet(mixed $offset): mixed {
			return $this->object[ $offset ] ?? null;
		}
		
		/**
		 * Set an offset
		 * @param mixed $offset The offset to set
		 * @param mixed $value The value to set
		 * @return void
		 */
		public function offsetSet(mixed $offset, mixed $value): void {
			$this->object[ $offset ] = $value;
		}
		
		/**
		 * Unset an offset
		 * @param mixed $offset The offset to unset
		 * @return void
		 */
		public function offsetUnset(mixed $offset): void {
			unset($this->object[ $offset ]);
		}
		
		/**
		 * Get a property value
		 * @param string $key The property to get
		 * @return mixed The property value
		 */
		public function __get(string $key): mixed {
			return $this->object[ $key ] ?? null;
		}
		
		/**
		 * Set a property
		 * @param string $key The property to set
		 * @param mixed $value The value to set
		 * @return void
		 */
		public function __set(string $key, mixed $value): void {
			$this->object[ $key ] = $value;
		}
		
		/**
		 * Check if a property is set
		 * @param string $key The property to check
		 * @return bool True if the property is set, false otherwise
		 */
		public function __isset(string $key): bool {
			return isset($this->object[ $key ]);
		}
		
		/**
		 * Unset a property
		 * @param string $key The property to unset
		 * @return void
		 */
		public function __unset(string $key): void {
			unset($this->object[ $key ]);
		}
	}