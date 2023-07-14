<?php
	declare(strict_types=1);
	
	namespace Magnetar\Helpers\Facades;
	
	/**
	 * @method write(string $path, string $contents, bool $overwrite=false): bool
	 * @method copy(string $path, string $destination, bool $overwrite=false): bool
	 * @method read(string $path): string|false
	 * @method exists(string $path): bool
	 * @method isFile(string $path): bool
	 * @method name(string $path): string
	 * @method basename(string $path): string
	 * @method extension(string $path): string
	 * @method mimetype(string $path): string|false
	 * @method lastModified(string $path): string|false
	 * @method append(string $path, string $contents): bool
	 * @method prepend(string $path, string $contents): bool
	 * @method move(string $source, string $destination, bool $overwrite=false): bool
	 * @method delete(string $path): bool
	 * @method isDirectory(string $path): bool
	 * @method copyDirectory(string $source, string $destination, bool $overwrite=false): bool
	 * @method emptyDirectory(string $path): bool
	 * @method deleteDirectory(string $path): bool
	 * 
	 * @see Magnetar\Filesystem\Filesystem
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