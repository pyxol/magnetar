<?php
	declare(strict_types=1);
	
	namespace Magnetar\Please;
	
	class DefaultCommandRegistry {
		/**
		 * The default command registry
		 * @var array<string, string>
		 */
		protected array $registry = [
			'facade:methods' => Actions\Facades\UpdateFacadePHPDocs::class,
		];
		
		/**
		 * Get the default command registry
		 * @return array<string, string>
		 */
		public function getRegistry(): array {
			return $this->registry;
		}
		
		/**
		 * Get the default command registry
		 * @return array<string, string>
		 */
		public function getCommands(): array {
			return array_keys($this->getRegistry());
		}
		
		/**
		 * Get the action class for a given command
		 * @param string $command The command to get the action class for
		 * @return string|null The action class or null if not found
		 */
		public function getCommandClass(string $command): string|null {
			return $this->registry[ $command ] ?? null;
		}
	}