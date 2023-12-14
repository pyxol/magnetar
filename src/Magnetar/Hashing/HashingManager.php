<?php
	declare(strict_types=1);
	
	namespace Magnetar\Hashing;
	
	use Exception;
	
	use Magnetar\Application;
	use Magnetar\Hashing\Hasher;
	use Magnetar\Hashing\BcryptHasher;
	use Magnetar\Hashing\ArgonHasher;
	use Magnetar\Hashing\Argon2IdHasher;
	
	class HashingManager {
		/**
		 * Known hashing algorithms
		 * @var array
		 */
		protected array $drivers = [
			'bcrypt' => BcryptHasher::class,
			'argon' => ArgonHasher::class,
			'argon2id' => Argon2IdHasher::class,
		];
		
		/**
		 * Constructor
		 */
		public function __construct(
			/**
			 * The application instance
			 * @var Application
			 */
			protected Application $app
		) {
			
		}
		
		/**
		 * Get a specific hashing driver
		 * @param string|null $driver The name of the driver
		 * @return Hasher
		 * 
		 * @throws \Exception
		 */
		public function driver(string|null $driver=null): Hasher {
			$driver ??= $this->getDefaultDriver();
			
			if(!isset($this->drivers[ $driver ])) {
				throw new Exception('Invalid hashing driver: '. $driver);
			}
			
			return new $this->drivers[ $driver ]($this->app);
		}
		
		/**
		 * Get the default hashing driver
		 * @return string
		 */
		public function getDefaultDriver(): string {
			return $this->app['config']['hashing.default'] ?? 'bcrypt';
		}
		
		/**
		 * Dynamically call a method on the default driver instance
		 * @param string $name The name of the method to call
		 * @param array $arguments The arguments to pass to the method
		 * @return mixed
		 */
		public function __call(string $name, array $arguments): mixed {
			return $this->driver()->{$name}(...$arguments);
		}
		
		/**
		 * Dynamically call a method on the default driver instance
		 * @param string $name The name of the method to call
		 * @param array $arguments The arguments to pass to the method
		 * @return void
		 */
		public static function __callStatic(string $name, array $arguments): mixed {
			return $this->driver()->{$name}(...$arguments);
		}
	}