<?php
	declare(strict_types=1);
	
	namespace Magnetar\Hashing;
	
	use Magnetar\Hashing\Hasher;
	
	class BcryptHasher extends Hasher {
		/**
		 * {@inheritDoc}
		 */
		public function hash(string $string): string {
			return password_hash(
				$string,
				$this->algorithm(),
				$this->hashOptions()
			);
		}
		
		/**
		 * {@inheritDoc}
		 */
		public function verify(string $string, string $hash): bool {
			return password_verify($string, $hash);
		}
		
		/**
		 * {@inheritDoc}
		 */
		protected function algorithm(): string {
			return PASSWORD_BCRYPT;
		}
		
		/**
		 * {@inheritDoc}
		 */
		protected function hashOptions(): array {
			return [
				'cost' => $this->cost(),
			];
		}
		
		/**
		 * Get the cost option
		 * @return int
		 */
		protected function cost(): int {
			return $this->app['config']['hashing.bcrypt.cost'] ?? 10;
		}
	}