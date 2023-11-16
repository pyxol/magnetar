<?php
	declare(strict_types=1);
	
	namespace Magnetar\Helpers\Facades;
	
	use Magnetar\Helpers\Facades\Facade;
	
	/**
	 * @method static \Magnetar\Filesystem\DiskAdapter connection(?string $connection_name)
	 * @method static ?string getDefaultDriveName()
	 * @method static array getConnected()
	 * @method static \Magnetar\Filesystem\DiskAdapter adapter(string $name)
	 * @method static \Magnetar\Filesystem\DiskAdapter drive(string $name)
	 * @method static string path(string $rel_path)
	 * @method static bool write(string $path, string $contents, bool $overwrite)
	 * @method static bool copy(string $source, string $destination, bool $overwrite)
	 * @method static string|false read(string $path)
	 * @method static bool exists(string $path)
	 * @method static bool isFile(string $path)
	 * @method static string name(string $path)
	 * @method static string basename(string $path)
	 * @method static string extension(string $path)
	 * @method static int size(string $path)
	 * @method static string|false mimetype(string $path)
	 * @method static string|false lastModified(string $path)
	 * @method static bool append(string $path, string $contents)
	 * @method static bool prepend(string $path, string $contents)
	 * @method static bool move(string $source, string $destination, bool $overwrite)
	 * @method static bool delete(array|string $paths)
	 * @method static bool directoryExists(string $path)
	 * @method static bool isDirectory(string $path)
	 * @method static bool makeDirectory(string $path, int $mode, bool $recursive)
	 * @method static bool copyDirectory(string $source, string $destination, bool $overwrite)
	 * @method static bool emptyDirectory(string $path)
	 * @method static bool deleteDirectory(string $path)
	 * @method static string url(string $path)
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