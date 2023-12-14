<?php
	declare(strict_types=1);
	
	namespace Magnetar\Hashing;
	
	use Magnetar\Hashing\ArgonHasher;
	
	class Argon2IdHasher extends ArgonHasher {
		/**
		 * {@inheritDoc}
		 */
		protected function algorithm(): string {
			return PASSWORD_ARGON2ID;
		}
	}