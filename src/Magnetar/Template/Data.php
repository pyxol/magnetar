<?php
	declare(strict_types=1);
	
	namespace Magnetar\Template;
	
	/**
	 * Data storage for a template instance
	 */
	class Data {
		protected array $data = [];
		
		/**
		 * Get a template variable. Returns null if not set
		 * @param string $name Variable name to get
		 * @return mixed
		 */
		public function __get(string $name): mixed {
			return $this->data[ $name ] ?? null;
		}
		
		/**
		 * Set a template variable
		 * @param string $name Variable name to set
		 * @param mixed $value Variable value to set
		 * @return void
		 */
		public function __set(string $name, mixed $value): void {
			$this->data[ $name ] = $value;
		}
		
		/**
		 * Check if a template variable is set
		 * @param string $name Variable name to check
		 * @return bool
		 */
		public function __isset(string $name): bool {
			return isset($this->data[ $name ]);
		}
		
		/**
		 * Unset a template variable
		 * @param string $name Variable name to unset
		 * @return void
		 */
		public function __unset(string $name): void {
			unset($this->data[ $name ]);
		}
		
		/**
		 * Return the template data as an array
		 * @return array
		 */
		public function toArray(): array {
			return $this->data;
		}
		
		/**
		 * Return the template data as a JSON string
		 * @return string
		 */
		public function toJson(): string {
			return json_encode($this->data);
		}
		
		/**
		 * Import new data into the template
		 * @param array $newData Assoc array of the new data to import
		 * @return void
		 */
		public function import(array $newData): void {
			$this->data = array_merge($this->data, $newData);
		}
	}