<?php
	declare(strict_types=1);
	
	namespace Magnetar\Helpers\Facades;
	
	use Magnetar\Helpers\Facades\Facade;
	
	/**
	 * @method set(string $key, mixed $value): void;
	 * @method get(string $key, mixed $default=null): mixed;
	 * @method has(string $key): bool;
	 * @method all(): array;
	 * @method setAll(array $values): void;
	 * @method remove(string $key): void;
	 * @method removeAll(): void;
	 * @method load(string $file, string|false $key=false): void;
	 * @method offsetExists(mixed $key): bool;
	 * @method offsetGet(mixed $key): mixed;
	 * @method offsetSet(mixed $key, mixed $value): void;
	 * @method offsetUnset(mixed $key): void;
	 * 
	 * @see Magnetar\Config\Config
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