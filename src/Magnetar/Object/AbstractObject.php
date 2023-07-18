<?php
	declare(strict_types=1);
	
	namespace Magnetar\Object;
	
	abstract class AbstractObject implements ArrayAccess {
		public ?array $object = null;
		
		public function __construct(
			int|null $id=null
		) {
			// pull object
			$this->pullObject($id);
		}
		
		// get the object from storage
		abstract protected function pullObject(int|null $id): void;
		
		
		public function offsetExists(mixed $offset): bool {
			return isset($this->object[ $offset ]);
		}
		
		public function offsetGet(mixed $offset): mixed {
			return $this->object[ $offset ] ?? null;
		}
		
		public function offsetSet(mixed $offset, mixed $value): void {
			$this->object[ $offset ] = $value;
		}
		
		public function offsetUnset(mixed $offset): void {
			unset($this->object[ $offset ]);
		}
		
		public function __get(string $key): mixed {
			return $this->object[ $key ] ?? null;
		}
		
		public function __set(string $key, mixed $value): void {
			$this->object[ $key ] = $value;
		}
		
		public function __isset(string $key): bool {
			return isset($this->object[ $key ]);
		}
		
		public function __unset(string $key): void {
			unset($this->object[ $key ]);
		}
	}