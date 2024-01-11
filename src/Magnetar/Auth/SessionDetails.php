<?php
	declare(strict_types=1);
	
	namespace Magnetar\Auth;
	
	use Magnetar\Application;
	use Magnetar\Auth\Exceptions\InvalidSessionDetailsException;
	use Magnetar\Encryption\Encryption;
	use Magnetar\Encryption\Exceptions\DecryptionException;
	use Magnetar\Encryption\Exceptions\EncryptionException;
	
	class SessionDetails {
		/**
		 * SessionDetails constructor
		 */
		public function __construct(
			protected Application $app,
			
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
			
			/**
			 * The user's session token
			 * @var string
			 */
			protected string $token
		) {
			// @TODO use model identifier key, not ID
			// @TODO use session token?
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
		
		/**
		 * Get the user's remember token
		 * @return string
		 */
		public function getToken(): string {
			return $this->token;
		}
		
		/**
		 * Determine if this object contains a valid set of session details.
		 * Only checks for the presence of required properties, not their validity
		 * @return bool
		 */
		public function isValid(): bool {
			if(!$this->id || empty($this->id)) {
				return false;
			}
			
			if(!$this->password || ('' === $this->password)) {
				return false;
			}
			
			if(!$this->token || ('' === $this->token)) {
				return false;
			}
			
			return true;
		}
		
		/**
		 * Get the encryption instance
		 * @return Encryption
		 */
		protected function encrypter(): Encryption {
			return $this->app->make('encryption');
		}
		
		/**
		 * Encrypt this object for storage in the session
		 * @return string
		 * 
		 * @throws \Magnetar\Auth\Exceptions\InvalidSessionDetailsException
		 */
		public function encryptForClient(): string {
			// confirm this object is valid
			if(!$this->isValid()) {
				throw new InvalidSessionDetailsException('Invalid session details');
			}
			
			// generate stored payload
			$payload = [
				'id' => $this->getId(),
				'password' => $this->getPassword(),
				'token' => $this->getToken()
			];
			
			try {
				return $this->encrypter()->encrypt($payload);
			} catch(EncryptionException $e) {
				throw new InvalidSessionDetailsException('Unable to encrypt session details');
			}
		}
		
		/**
		 * Decrypt a string from the session into a SessionDetails object
		 * @param string $encrypted The encrypted string from the session
		 * @return static
		 * 
		 * @throws \Magnetar\Auth\Exceptions\InvalidSessionDetailsException
		 */
		public static function decryptFromClient(string $encrypted): static {
			try {
				$payload = $this->encrypter()->decrypt($encrypted);
			} catch(DecryptionException $e) {
				throw new InvalidSessionDetailsException('Unable to decrypt session details');
			}
			
			// confirm payload is valid
			if(!isset($payload['id']) || !isset($payload['password']) || !isset($payload['token'])) {
				throw new InvalidSessionDetailsException('Invalid session details message');
			}
			
			return new static(
				$this->app,
				(int) $payload['id'],
				(string) $payload['password'],
				(string) $payload['token']
			);
		}
	}