<?php
	declare(strict_types=1);
	
	namespace Magnetar\Model;
	
	use Magnetar\Helpers\Facades\DB;
	
	/**
	 * Model trait for lookup functions
	 */
	trait HasLookupTrait {
		/**
		 * Find a model by ID
		 * @param int $id The ID of the model to find
		 * @return static
		 */
		public function find(int $id): static {
			// pull model data
			$this->_data = DB::connection($this->connection_name)
				->table($this->table)
				->where($this->identifier, $id)
				->fetchOne();
			
			return $this;
		}
	}