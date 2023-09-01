<?php
	declare(strict_types=1);
	
	namespace Magnetar\Object;
	
	use ArrayAccess;
	
	abstract class AbstractObject implements ArrayAccess {
		public ?array $object = null;
		
		/**
		 * AbstractObject constructor.
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
		 * @param mixed $offset
		 * @return bool
		 */
		public function offsetExists(mixed $offset): bool {
			return isset($this->object[ $offset ]);
		}
		
		/**
		 * Get an offset
		 * @param mixed $offset
		 * @return mixed
		 */
		public function offsetGet(mixed $offset): mixed {
			return $this->object[ $offset ] ?? null;
		}
		
		/**
		 * Set an offset
		 * @param mixed $offset
		 * @param mixed $value
		 */
		public function offsetSet(mixed $offset, mixed $value): void {
			$this->object[ $offset ] = $value;
		}
		
		/**
		 * Unset an offset
		 * @param mixed $offset
		 */
		public function offsetUnset(mixed $offset): void {
			unset($this->object[ $offset ]);
		}
		
		/**
		 * Get a property
		 * @param string $key
		 * @return mixed
		 */
		public function __get(string $key): mixed {
			return $this->object[ $key ] ?? null;
		}
		
		/**
		 * Set a property
		 * @param string $key
		 * @param mixed $value
		 * @return void
		 */
		public function __set(string $key, mixed $value): void {
			$this->object[ $key ] = $value;
		}
		
		/**
		 * Check if a property is set
		 * @param string $key
		 * @return bool
		 */
		public function __isset(string $key): bool {
			return isset($this->object[ $key ]);
		}
		
		/**
		 * Unset a property
		 * @param string $key
		 * @return void
		 */
		public function __unset(string $key): void {
			unset($this->object[ $key ]);
		}
	}