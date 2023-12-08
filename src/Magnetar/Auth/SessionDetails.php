<?php
	declare(strict_types=1);
	
	namespace Magnetar\Auth;
	
	class SessionDetails {
		/**
		 * SessionDetails constructor
		 */
		public function __construct(
			/**
			 * The user's ID
			 * @var int
			 */
			protected int $id,
			
			/**
			 * The user's encrypted password
			 * @var string
			 */
			protected string $password,
		) {
			// @TODO use model identifier key, not ID
			// @TODO use session token?
		}
		
		/**
		 * Factory method to create a new SessionDetails instance
		 * @param int $id
		 * @param string $password
		 * @return static
		 */
		public static function make(
			/**
			 * The user's ID
			 * @var int
			 */
			int $id,
			
			/**
			 * The user's encrypted password
			 * @var string
			 */
			string $password,
		): static {
			return new static($id, $password);
		}
		
		/**
		 * Get the user's ID
		 * @return int
		 */
		public function getId(): int {
			return $this->id;
		}
		
		/**
		 * Get the user's encrypted password
		 * @return string
		 */
		public function getPassword(): string {
			return $this->password;
		}
		
	}