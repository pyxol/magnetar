<?php
	declare(strict_types=1);
	
	namespace Magnetar\Auth;
	
	use Magnetar\Utilities\Cryptography\Scramble;
	use Magnetar\Auth\Exceptions\InvalidSessionDetailsException;
use Magnetar\Utilities\Cryptography\Encryption;

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
			
			/**
			 * The user's session token
			 * @var string
			 */
			string $token
		): static {
			return new static($id, $password, $token);
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
		 * Encrypt this object for storage in the session
		 * @return string
		 */
		public function encryptForClient(): string {
			// @TODO needs closer inspection
			
			// confirm this object is valid
			if(!$this->isValid()) {
				throw new InvalidSessionDetailsException('Invalid session details');
			}
			
			// generate stored message
			$message = implode('|', [
				$this->getId(),
				$this->getPassword(),
				$this->getToken()
			]);
			
			// message validation
			$mac = hash_hmac('sha256', $message, app()->config('app.key'));
			
			$encrypted_message = (new Encryption(
				app()->config('app.key'),
				null,
				app()->config('app.cipher')
			))->encrypt([
				'mac' => $mac,
				'message' => $message,
			]);
			
			if(false === $encrypted_message) {
				throw new InvalidSessionDetailsException('Unable to encrypt session details');
			}
			
			return Scramble::encode(
				$encrypted_message
			);
		}
		
		/**
		 * Decrypt a string from the session into a SessionDetails object
		 * @param string $encrypted The encrypted string from the session
		 * @return static
		 */
		public static function decryptFromClient(string $encrypted): static {
			// @TODO needs closer inspection
			
			$soft_decrypted = Scramble::decode($encrypted);
			
			if(false === $soft_decrypted) {
				throw new InvalidSessionDetailsException('Unable to initially decrypt session details');
			}
			
			$decrypted = (new Encryption(
				app()->config('app.key'),
				null,
				app()->config('app.cipher')
			))->decrypt($soft_decrypted);
			
			if(false === $decrypted) {
				throw new InvalidSessionDetailsException('Unable to decrypt session details');
			}
			
			// validation
			if(!isset($decrypted['mac']) || !isset($decrypted['message'])) {
				throw new InvalidSessionDetailsException('Invalid encrypted session details');
			}
			
			// mac validation
			$mac = hash_hmac('sha256', $decrypted['message'], app()->config('app.key'));
			
			if(!hash_equals($mac, $decrypted['mac'])) {
				throw new InvalidSessionDetailsException('Encrypted session details failed validation');
			}
			
			$parts = explode('|', $decrypted['message']);
			
			if(count($parts) < 3) {
				throw new InvalidSessionDetailsException('Invalid session details message');
			}
			
			return new static(
				(int) $parts[0],
				(string) $parts[1],
				(string) $parts[2]
			);
		}
	}