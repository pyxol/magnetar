<?php
	declare(strict_types=1);
	
	namespace Magnetar\Model;
	
	use Magnetar\Model\Model;
	use Magnetar\Model\Exceptions\ModelException;
	use Magnetar\Helpers\Facades\DB;
	
	/**
	 * Model trait for mutable functions
	 */
	trait HasMutableTrait {
		/**
		 * Create a new model instance using the provided attributes and save it to the database. Only accessed statically
		 * @param array $attributes The attributes to use for the model
		 * @return Model
		 */
		private function create(array $attributes): Model {
			if(!empty($attributes[ $this->identifier ])) {
				throw new ModelException('Cannot create a model using attributes that include the identifier');
			}
			
			$this->_data = $attributes;
			
			// save
			$this->_data[ $this->identifier ] = DB::connection($this->connection_name)
				->table($this->table)
				->insert($attributes);
			
			return $this;
		}
		
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