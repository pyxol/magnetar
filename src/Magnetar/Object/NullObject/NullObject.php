<?php
	declare(strict_types=1);
	
	namespace Magnetar\Object\NullObject;
	
	use Exception;
	
	use Magnetar\Object\AbstractObject;
	
	/**
	 * A sample Null object
	 */
	class NullObject extends AbstractObject {
		/**
		 * Filler method for pulling an object from storage
		 * @return void
		 * 
		 * @throws Exception
		 */
		protected function pullObject(int|null $id): void {
			// nothing needed for this method in this class
		}
	}