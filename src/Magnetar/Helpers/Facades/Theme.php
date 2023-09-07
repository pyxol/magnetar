<?php
	declare(strict_types=1);
	
	namespace Magnetar\Helpers\Facades;
	
	use Magnetar\Helpers\Facades\Facade;
	
	/**
	 * @method clear(): void;
	 * @method delete(string $key): bool;
	 * @method get(string $key): mixed;
	 * @method getMany(array $keys): array;
	 * @method increment(string $key, int $step=1): int|false;
	 * @method decrement(string $key, int $step=1): int|false;
	 * @method has(string $key): bool;
	 * @method hasMany(array $keys): array;
	 * @method set(string $key, $value, int $ttl=0): mixed;
	 * @method setMany(array $values, int $ttl=0): void;
	 * 
	 * @see Magnetar\Template\ThemeManager
	 */
	class Theme extends Facade {
		/**
		 * Get the named key that this facade represents
		 * @return string
		 */
		protected static function getFacadeKey(): string {
			return 'theme';
		}
	}