<?php
	declare(strict_types=1);
	
	namespace Magnetar\Hashing;
	
	use Magnetar\Hashing\Hasher;
	
	class ArgonHasher extends Hasher {
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
			return PASSWORD_ARGON2I;
		}
		
		/**
		 * {@inheritDoc}
		 */
		protected function hashOptions(): array {
			return [
				'memory_cost' => $this->memory_cost(),
				'time_cost' => $this->time_cost(),
				'threads' => $this->threads(),
			];
		}
		
		/**
		 * Get the memory cost option
		 * @return int
		 */
		protected function memory_cost(): int {
			return $this->app['config']['hashing.argon.memory_cost'] ?? 1024;
		}
		
		/**
		 * Get the time cost option
		 * @return int
		 */
		protected function time_cost(): int {
			return $this->app['config']['hashing.argon.time_cost'] ?? 2;
		}
		
		/**
		 * Get the threads option
		 * @return int
		 */
		protected function threads(): int {
			return $this->app['config']['hashing.argon.threads'] ?? 2;
		}
	}