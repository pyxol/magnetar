<?php
	declare(strict_types=1);
	
	namespace Magnetar\Helpers\Facades;
	
	use Magnetar\Helpers\Facades\Facade;
	
	/**
	 * @method connection(?string $connection_name=null): Magnetar\Filesystem\DiskAdapter
	 * @method getDefaultDriveName(): ?string
	 * @method getConnected(): array
	 * @method adapter(string $name): Magnetar\Filesystem\DiskAdapter
	 * @method drive(string $name): Magnetar\Filesystem\DiskAdapter
	 * @method path(string $rel_path=''): string
	 * @method write(string $path, string $contents, bool $overwrite=false): bool
	 * @method copy(string $source, string $destination, bool $overwrite=false): bool
	 * @method read(string $path): string|false
	 * @method exists(string $path): bool
	 * @method isFile(string $path): bool
	 * @method name(string $path): string
	 * @method basename(string $path): string
	 * @method extension(string $path): string
	 * @method size(string $path): int
	 * @method mimetype(string $path): string|false
	 * @method lastModified(string $path): string|false
	 * @method append(string $path, string $contents): bool
	 * @method prepend(string $path, string $contents): bool
	 * @method move(string $source, string $destination, bool $overwrite=false): bool
	 * @method delete(array|string $paths): bool
	 * @method directoryExists(string $path): bool
	 * @method isDirectory(string $path): bool
	 * @method makeDirectory(string $path, int $mode=511, bool $recursive=false): bool
	 * @method copyDirectory(string $source, string $destination, bool $overwrite=false): bool
	 * @method emptyDirectory(string $path): bool
	 * @method deleteDirectory(string $path): bool
	 * @method url(string $path): string
	 * 
	 * @see \Magnetar\Filesystem\ConnectionManager
	 * @see \Magnetar\Filesystem\Disk\DiskAdapter
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