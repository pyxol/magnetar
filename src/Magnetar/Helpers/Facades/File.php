<?php
	declare(strict_types=1);
	
	namespace Magnetar\Helpers\Facades;
	
	use Magnetar\Helpers\Facades\Facade;
	
	/**
	 * @method connection(?string $connection_name=null): Magnetar\Filesystem\DiskAdapter;
	 * @method getDefaultDriveName(): ?string;
	 * @method getConnected(): array;
	 * @method adapter(string $name): Magnetar\Filesystem\DiskAdapter;
	 * @method drive(string $name): Magnetar\Filesystem\DiskAdapter;
	 * 
	 * @see Magnetar\Filesystem\ConnectionManager
	 */
	class File extends Facade {
		/**
		 * Get the named key that this facade represents
		 * @return string
		 */
		protected static function getFacadeKey(): string {
			return 'files';
		}
	}