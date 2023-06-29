<?php
	declare(strict_types=1);
	
	namespace Magnetar\Object\Entity;
	
	use Magnetar\Object\AbstractObject;
	use Magnetar\Object\EntityType\EntityType;
	use Magnetar\Object\Media\Media;
	use Exception;
	
	class NullObject extends AbstractObject {
		/**
		 * Filler method for pulling an object from storage
		 * @return void
		 * @throws Exception
		 */
		protected function pullObject(int $id): void {
			// nothing needed for this method in this class
		}
	}