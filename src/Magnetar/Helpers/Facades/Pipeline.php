<?php
	declare(strict_types=1);
	
	namespace Magnetar\Helpers\Facades;
	
	use Magnetar\Helpers\Facades\Facade;
	
	/**
	 * @method static static send(mixed $thing)
	 * @method static static through(array $pipes)
	 * @method static static using(string $method)
	 * @method static mixed then(Closure $destination)
	 * @method static mixed thenReturn()
	 * 
	 * @see \Magnetar\Pipeline\Pipeline
	 */
	class Pipeline extends Facade {
		/**
		 * @{inheritDoc}
		 */
		protected static bool $cached = false;
		
		/**
		 * @{inheritDoc}
		 */
		protected static function getFacadeKey(): string {
			return 'pipeline';
		}
	}