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
	}