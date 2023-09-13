<?php
	declare(strict_types=1);
	
	namespace Magnetar\Helpers\Facades;
	
	use Magnetar\Helpers\Facades\Facade;
	
	/**
	 * @method connection(?string $connection_name=null): Magnetar\Queue\QueueAdapter;
	 * @method getDefaultConnectionName(): ?string;
	 * @method getConnected(): array;
	 * @method adapter(string $connection_name): Magnetar\Queue\QueueAdapter;
	 * 
	 * @see Magnetar\Queue\QueueManager
	 */
	class Queue extends Facade {
		/**
		 * Get the named key that this facade represents
		 * @return string
		 */
		protected static function getFacadeKey(): string {
			return 'queue';
		}
	}