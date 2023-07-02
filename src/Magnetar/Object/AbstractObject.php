<?php
	declare(strict_types=1);
	
	namespace Magnetar\Object;
	
	use Magnetar\Database\AbstractDatabase;
	
	abstract class AbstractObject {
		protected ?AbstractDatabase $db = null;
		public ?array $object = null;
		
		public function __construct(
			int|null $id=null,
			?AbstractDatabase $db=null
		) {
			if(!is_null($db)) {
				$this->setDb($db);
			}
			
			// pull object
			$this->pullObject($id);
			
			return $this;
		}
		
		/**
		 * Set the database connection
		 * @param AbstractDatabase $db
		 */
		public function setDb(AbstractDatabase $db): void {
			$this->db = $db;
		}
		
		// getter
		public function __get(string $key): mixed {
			return $this->object[$key] ?? null;
		}
		
		// setter
		public function __set(string $key, mixed $value): void {
			$this->object[ $key ] = $value;
		}
		
		// isseter
		public function __isset(string $key): bool {
			return isset($this->object[ $key ]);
		}
		
		
		// get the object from storage
		abstract protected function pullObject(int|null $id): void;
	}