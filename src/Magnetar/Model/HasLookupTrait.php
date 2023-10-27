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
		 * @param int|string $id The ID of the model to find
		 * @return static
		 * 
		 * @throws ModelNotFoundException
		 */
		private function find(int|string $id): static {
			// pull model data
			$data = DB::connection($this->connection_name)
				->table($this->table)
				->where($this->identifier, $id)
				->fetchOne();
			
			if(false === $data) {
				throw new Exceptions\ModelNotFoundException('Model not found in table ['. $this->table .'] with identifier ['. $id .']');
			}
			
			return new static($data);
		}
		
		/**
		 * Find a model by ID or return a specified default value
		 * @param int|string $id The ID of the model to find
		 * @param mixed $default The default value to return if the model is not found
		 * @return mixed
		 */
		private function findOr(int|string $id, mixed $default): mixed {
			try {
				return $this->find($id);
			} catch(ModelNotFoundException $e) {
				return $default;
			}
		}
		
		/**
		 * Find a model by ID or return null
		 * @param int|string $id The ID of the model to find
		 * @return static|null
		 */
		private function findOrNull(int|string $id): ?static {
			return $this->findOr($id, null);
		}
		
		/**
		 * Find a model by ID or return false
		 * @param int|string $id The ID of the model to find
		 * @return static|false
		 */
		private function findOrFalse(int|string $id): static|false {
			return $this->findOr($id, false);
		}
	}