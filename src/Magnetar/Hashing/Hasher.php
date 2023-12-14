<?php
	declare(strict_types=1);
	
	namespace Magnetar\Hashing;
	
	use RuntimeException;
	
	use Magnetar\Application;
	
	class Hasher {
		public function __construct(
			/**
			 * The application instance
			 * @var Application
			 */
			protected Application $app
		) {
			
		}
		
		/**
		 * Hash the given value
		 * @param string $string The value to hash
		 * @return string
		 * 
		 * @throws \RuntimeException
		 */
		public function hash(string $string): string {
			throw new RuntimeException('Driver should override this method');
		}
		
		/**
		 * Check the given plain value against a hashed value
		 * @param string $string The plain value to check
		 * @param string $hash The hashed value to check against
		 * @return bool
		 * 
		 * @throws \RuntimeException
		 */
		public function verify(string $string, string $hash): bool {
			throw new RuntimeException('Driver should override this method');
		}
		
		/**
		 * Get information about a hashed value
		 * @param string $hashValue The hashed value to get info about
		 * @return array
		 * 
		 * @see https://www.php.net/manual/en/function.password-get-info.php
		 */
		public function info(string $hashValue): array {
			return password_get_info($hashValue);
		}
		
		/**
		 * Get the algorithm option
		 * @return string
		 * 
		 * @see https://www.php.net/manual/en/function.password-hash.php
		 * 
		 * @throws \RuntimeException
		 */
		protected function algorithm(): string {
			throw new RuntimeException('Driver should override this method');
		}
		
		/**
		 * Get the hashing options used by password_hash
		 * @return array
		 * 
		 * @see https://www.php.net/manual/en/function.password-hash.php
		 */
		protected function hashOptions(): array {
			return [];
		}
		
		/**
		 * Check if the given hash needs to be rehashed according to the options of the hasher
		 * @param string $hashValue
		 * @return bool
		 */
		public function needsRehash(string $hashValue, array|null $override_options=null): bool {
			return password_needs_rehash(
				$hashValue,
				$this->algorithm(),
				$override_options ?? $this->hashOptions()
			);
		}
	}