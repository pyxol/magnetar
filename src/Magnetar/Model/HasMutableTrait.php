<?php
	declare(strict_types=1);
	
	namespace Magnetar\Model;
	
	use Magnetar\Helpers\Facades\DB;
	
	/**
	 * Model trait for mutable functions
	 */
	trait HasMutableTrait {
		/**
		 * Save the model to the database. If the model has no ID, creates a new record, otherwise update
		 * @return void
		 */
		public function save(): void {
			// if the model is not dirty, return
			if(!$this->isDirty()) {
				return;
			}
			
			// save to database
			if(!empty($this->_data[ $this->identifier ])) {
				// update
				$this->_update();
			} else {
				// model has no ID defined, insert
				$this->_insert();
			}
			
			// clean
			$this->clearDirty();
		}
		
		/**
		 * Insert the model data into the database
		 * @return void
		 */
		protected function _insert(): void {
			$insert_id = DB::connection($this->connection_name)
				->table($this->table)
				->insert($this->_data);
			
			// set the ID
			$this->_data[ $this->identifier ] = $insert_id;
		}
		
		/**
		 * Insert the model's data into the database
		 * @return void
		 */
		protected function _update(): void {
			DB::connection($this->connection_name)
				->table($this->table)
				->where($this->identifier, $this->_data[ $this->identifier ])
				->update($this->getDirtyAttributes());
			
		}
		
		/**
		 * Delete the model from the database
		 * @return void
		 */
		public function delete(): void {
			DB::connection($this->connection_name)
				->table($this->table)
				->where($this->identifier, $this->_data[ $this->identifier ])
				->delete();
			
			// clear data
			$this->_data = [];
			
			// clear dirty
			$this->clearDirty();
		}
	}