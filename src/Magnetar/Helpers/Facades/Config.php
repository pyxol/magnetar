<?php
	declare(strict_types=1);
	
	namespace Magnetar\Helpers\Facades;
	
	use Magnetar\Helpers\Facades\Facade;
	
	/**
	 * @method static void set(string $key, mixed $value)
	 * @method static mixed get(string $key, mixed $default)
	 * @method static bool has(string $key)
	 * @method static array all()
	 * @method static void setAll(array $values)
	 * @method static void remove(string $key)
	 * @method static void removeAll()
	 * @method static void load(string $file, string|false $key)
	 * @method static bool offsetExists(mixed $key)
	 * @method static mixed offsetGet(mixed $key)
	 * @method static void offsetSet(mixed $key, mixed $value)
	 * @method static void offsetUnset(mixed $key)
	 * 
	 * @see \Magnetar\Config\Config
	 */
	class Config extends Facade {
		/**
		 * Get the named key that this facade represents
		 * @return string
		 */
		protected static function getFacadeKey(): string {
			return 'config';
		}
	}