<?php
	declare(strict_types=1);
	
	namespace Magnetar\Model;
	
	use Magnetar\Helpers\Facades\DB;
	use Magnetar\Model\Exceptions\ModelNotFoundException;
	
	/**
	 * Model trait for lookup functions
	 */
	trait HasLookupTrait {
		/**
		 * Find a model by ID
		 * @param array|string|int $id The ID of the model to find. If an array is passed, this will match all keys (model columns) in their values
		 * @return static
		 * 
		 * @throws ModelNotFoundException
		 */
		private function find(array|string|int $id): static {
			// pull model data
			$query = DB::connection($this->connection_name)
				->table($this->getTable());
			
			if(is_array($id)) {
				if(empty($id)) {
					throw new Exceptions\ModelNotFoundException('Model not found in table ['. $this->getTable() .'] using empty identifier');
				}
				
				foreach($id as $key => $value) {
					$query = $query->where($key, $value);
				}
			} else {
				$query = $query->where($this->getIdentifier(), $id);
			}
			
			$data = $query->fetchOne();
			
			if(false === $data) {
				throw new Exceptions\ModelNotFoundException('Model not found in table ['. $this->getTable() .'] with identifier ['. $id .']');
			}
			
			return new static($data);
		}
		
		/**
		 * Find a model by ID or return a specified default value
		 * @param array|string|int $id The ID of the model to find
		 * @param mixed $default The default value to return if the model is not found
		 * @return mixed
		 * 
		 * @see \Magnetar\Model\HasLookupTrait::find()
		 */
		private function findOr(array|string|int $id, mixed $default): mixed {
			try {
				return $this->find($id);
			} catch(ModelNotFoundException $e) {
				return $default;
			}
		}
		
		/**
		 * Find a model by ID or return null
		 * @param array|string|int $id The ID of the model to find
		 * @return static|null
		 * 
		 * @see \Magnetar\Model\HasLookupTrait::find()
		 */
		private function findOrNull(array|string|int $id): ?static {
			return $this->findOr($id, null);
		}
		
		/**
		 * Find a model by ID or return false
		 * @param array|string|int $id The ID of the model to find
		 * @return static|false
		 * 
		 * @see \Magnetar\Model\HasLookupTrait::find()
		 */
		private function findOrFalse(array|string|int $id): static|false {
			return $this->findOr($id, false);
		}
	}