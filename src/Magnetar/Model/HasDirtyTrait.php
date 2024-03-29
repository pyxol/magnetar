<?php
	declare(strict_types=1);
	
	namespace Magnetar\Model;
	
	/**
	 * Model trait for dirty functions and keeping track of dirty state/attributes
	 */
	trait HasDirtyTrait {
		/**
		 * State if the model is dirty
		 * @var bool
		 */
		protected bool $_dirty = false;
		
		/**
		 * The dirty attributes for the model
		 * @var array
		 */
		protected array $_dirty_attributes = [];
		
		/**
		 * Determine if the model is dirty
		 * @return bool
		 */
		public function isDirty(string|null $attribute=null): bool {
			if(null !== $attribute) {
				return $this->hasDirtyAttribute($attribute);
			}
			
			return $this->_dirty;
		}
		
		/**
		 * Determine if the model (attribute) is clean
		 * @param string|null $attribute The attribute name to check for dirtiness
		 * @return bool
		 */
		public function isClean(string|null $attribute=null): bool {
			return !$this->isDirty($attribute);
		}
		
		/**
		 * Get the dirty attributes for the model
		 * @return array
		 */
		public function getDirty(): array {
			return $this->_dirty_attributes;
		}
		
		/**
		 * Determine if the model has a dirty attribute
		 * @param string $attribute The attribute name to check for dirtiness
		 * @return bool
		 */
		public function hasDirtyAttribute(string $attribute): bool {
			return $this->_dirty_attributes[ $attribute ] ?? false;
		}
		
		/**
		 * Set a dirty attribute
		 * @param string $attribute The attribute name to mark as dirty
		 * @param mixed $value The value to set
		 * @return void
		 */
		public function setDirtyAttribute(string $attribute, mixed $value): void {
			$this->_dirty_attributes[ $attribute ] = true;
			
			$this->_dirty = true;
		}
		
		/**
		 * Remove a dirty attribute from the model
		 * @param string $attribute The attribute name to mark as clean
		 * @return void
		 */
		public function removeDirtyAttribute(string $attribute): void {
			unset($this->_dirty_attributes[ $attribute ]);
			
			if(empty($this->_dirty_attributes)) {
				$this->_dirty = false;
			}
		}
		
		/**
		 * Clear all dirty attributes
		 * @return void
		 */
		public function clearDirty(): void {
			$this->_dirty_attributes = [];
			$this->_dirty = false;
		}
	}