<?php
	declare(strict_types=1);
	
	namespace Magnetar\Model;
	
	use Magnetar\Model\Model;
	
	/**
	 * Model trait for comparing two models
	 */
	trait HasComparableTrait {
		/**
		 * Determine if the model is equal to another model
		 * @return bool
		 */
		public function is(Model $model): bool {
			if(get_class($model) !== get_class($this)) {
				return false;
			}
			
			return ($this->{$this->identifier} === $model->{$model->identifier});
		}
		
		/**
		 * Determine if the model is not equal to another model
		 * @param Model $model The model to compare against
		 * @return bool
		 */
		public function isNot(Model $model): bool {
			return !$this->is($model);
		}
	}