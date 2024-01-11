<?php
	declare(strict_types=1);
	
	namespace Magnetar\Encryption;
	
	use Magnetar\Encryption\Exceptions\EncryptionException;
	use Magnetar\Utilities\Str;
	
	/**
	 * Encryption utility static class
	 */
	class Encryption {
		/**
		 * The digest method to use for encryption/decryption.
		 * @var string
		 * 
		 * @see https://www.php.net/manual/en/function.openssl-get-md-methods.php
		 */
		protected string $digest_method = 'SHA256';
		
		/**
		 * The cipher method to use for encryption/decryption.
		 * @var string
		 * 
		 * @see https://www.php.net/manual/en/function.openssl-get-cipher-methods.php
		 */
		protected string $cipher_method = 'AES-256-CBC';
		
		/**
		 * Encryption constructor.
		 * @param string $salt Effectively a 'password' to an encrypted block of text. Encrypting with one salt and decrypting with another will result in garbage data.
		 * @param string|null $digest_method Optional. A value from openssl_get_md_methods()
		 * @param string|null $cipher_method Optional. A value from openssl_get_cipher_methods()
		 * 
		 * @see https://www.php.net/manual/en/function.openssl-get-md-methods.php
		 * @see https://www.php.net/manual/en/function.openssl-get-cipher-methods.php
		 */
		public function __construct(
			protected string $salt,
			string|null $digest_method=null,
			string|null $cipher_method=null
		) {
			if(str_contains($this->salt, 'base64:')) {
				$this->salt = base64_decode(substr(trim($this->salt), 7));
			}
			
			if(null !== $digest_method) {
				$this->digest_method = $digest_method;
			}
			
			if(null !== $cipher_method) {
				$this->cipher_method = $cipher_method;
			}
		}
		
		/**
		 * Encrypt a string with config-based key/settings that can passed on and be decrypted via Encryption->decrypt. Returns false on error (usually from bad digest_method/cipher_method settings)
		 * @param string $string String to encrypt
		 * @param bool $serialize Whether to serialize the string before encrypting it
		 * @return string|false
		 * 
		 * @throws \Magnetar\Encryption\Exceptions\EncryptionException
		 */
		public function encrypt(string $string, bool $serialize=true): string|false {
			// generate initialization vector
			$iv = random_bytes(openssl_cipher_iv_length($this->cipher_method));
			
			// encrypt value
			$value = openssl_encrypt(
				$serialize ? serialize($string) : $string,
				strtolower($this->cipher_method),
				openssl_digest($this->salt, $this->digest_method, true),
				0,
				$iv,
				$tag
			);
			
			if(false === $value) {
				throw new EncryptionException('Failed to encrypt data');
			}
			
			// encode iv and tag
			$iv = base64_encode($iv);
			$tag = base64_encode($tag ?? '');
			
			// generate mac
			$mac = $this->hash($iv, $value);
			
			$json = json_encode(compact('iv', 'value', 'mac', 'tag'), JSON_UNESCAPED_SLASHES);
			
			if(JSON_ERROR_NONE !== json_last_error()) {
				throw new EncryptionException('Failed to generate cargo of encrypted data');
			}
			
			return base64_encode($json);
		}
		
		/**
		 * Decrypt a previously encrypted string from Encryption->encrypt. Returns false on error (mostly from wrong encrypted data or a changed encryption key, or sometimes from bad digest_method/cipher_method settings)
		 * @param string $payload Payload to decrypt
		 * @param bool $unserialize Whether to unserialize the decrypted string
		 * @return string|false
		 * 
		 * @throws \Magnetar\Encryption\Exceptions\EncryptionException
		 */
		public function decrypt(string $payload, bool $unserialize=true): string|false {
			$payload = json_decode(base64_decode($payload), true);
			
			if(JSON_ERROR_NONE !== json_last_error()) {
				throw new EncryptionException('Failed to decode encrypted data');
			}
			
			if(!isset($payload['iv'], $payload['value'], $payload['mac'], $payload['tag'])) {
				throw new EncryptionException('Failed to decode encrypted data');
			}
			
			// decode iv and tag
			$iv = base64_decode($payload['iv']);
			$tag = base64_decode($payload['tag']);
			
			// validate mac
			if(!$this->isValidMac($payload)) {
				throw new EncryptionException('Failed to validate mac');
			}
			
			// decrypt value
			$value = openssl_decrypt(
				$payload['value'],
				strtolower($this->cipher_method),
				openssl_digest($this->salt, $this->digest_method, true),
				0,
				$iv,
				$tag
			);
			
			if(false === $value) {
				throw new EncryptionException('Failed to decrypt data');
			}
			
			return ($unserialize?unserialize($value):$value);
		}
		
		/**
		 * Hash a string with the salt
		 * @param string $iv Initialization vector
		 * @param string $value Value to hash
		 * @return string
		 */
		protected function hash(string $iv, string $value): string {
			return hash_hmac(
				'sha256',
				$iv . $value,
				$this->salt
			);
		}
		
		/**
		 * Validate a decrypted payload
		 * @param mixed $payload Payload to validate
		 * @return bool
		 */
		protected function isValidPayload(mixed $payload): bool {
			if(!is_array($payload)) {
				return false;
			}
			
			foreach(['iv', 'value', 'mac'] as $key) {
				if(!isset($payload[ $key ]) || !is_string($payload[ $key ])) {
					return false;
				}
			}
			
			if(isset($payload['tag']) && !is_string($payload['tag'])) {
				return false;
			}
			
			if(strlen(base64_decode($payload['iv'], true)) !== openssl_cipher_iv_length(strtolower($this->cipher_method))) {
				return false;
			}
			
			return true;
		}
		
		/**
		 * Validate the mac of a decrypted payload
		 * @param array $payload Payload to validate
		 * @return bool
		 */
		protected function isValidMac(array $payload): bool {
			return hash_equals(
				$payload['mac'],
				$this->hash($payload['iv'], $payload['value'])
			);
		}
	}