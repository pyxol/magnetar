<?php
	declare(strict_types=1);
	
	namespace Magnetar\Encryption;
	
	use Magnetar\Encryption\Exceptions\EncryptionException;
	use Magnetar\Encryption\Exceptions\DecryptionException;
	
	/**
	 * Encryption class
	 */
	class Encryption {
		protected string $key = '';
		
		/**
		 * The digest method to use for mac generation.
		 * @var string
		 * 
		 * @see https://www.php.net/manual/en/function.hash-hmac-algos.php
		 */
		protected string $digest_algo = 'sha256';
		
		/**
		 * The cipher method to use for encryption/decryption.
		 * @var string
		 * 
		 * @see https://www.php.net/manual/en/function.openssl-get-cipher-methods.php
		 */
		protected string $cipher_method = 'aes-256-cbc';
		
		/**
		 * Encryption constructor.
		 * @param string $key The key to use for encryption/decryption. If the key is prefixed with "base64:", it will be base64 decoded.
		 * @param string|null $cipher_method Optional. A value from \openssl_get_cipher_methods()
		 * @param string|null $digest_algo Optional. A value from \hash_hmac_algos()
		 * 
		 * @see https://www.php.net/manual/en/function.openssl-get-md-methods.php
		 * @see https://www.php.net/manual/en/function.openssl-get-cipher-methods.php
		 */
		public function __construct(
			string $key='',
			string|null $cipher_method=null,
			string|null $digest_algo=null
		) {
			if(str_contains($this->key, 'base64:')) {
				$this->key = base64_decode(substr(trim($key), 7));
			} else {
				$this->key = $key;
			}
			
			if(null !== $cipher_method) {
				$this->cipher_method = strtolower($cipher_method);
			}
			
			if(null !== $digest_algo) {
				$this->digest_algo = $digest_algo;
			}
		}
		
		/**
		 * Generate a random key
		 * @return string
		 */
		public static function generateKey(): string {
			return base64_encode(random_bytes(32));
		}
		
		/**
		 * Encrypt a variable using this app's encryption config
		 * @param mixed $value What's being encrypted
		 * @param bool $serialize Whether to serialize the string before encrypting it
		 * @return string
		 * 
		 * @throws \Magnetar\Encryption\Exceptions\EncryptionException
		 */
		public function encrypt(string $string, bool $serialize=true): string {
			// generate initialization vector
			$iv = random_bytes(openssl_cipher_iv_length(strtolower($this->cipher_method)));
			
			// encrypt value
			$value = openssl_encrypt(
				$serialize ? serialize($string) : $string,
				strtolower($this->cipher_method),
				$this->key,
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
			
			// package into a base64 encoded json string
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
		 * @return mixed
		 * 
		 * @throws \Magnetar\Encryption\Exceptions\DecryptionException
		 */
		public function decrypt(string $payload, bool $unserialize=true): mixed {
			$payload = json_decode(base64_decode($payload), true);
			
			if(JSON_ERROR_NONE !== json_last_error()) {
				throw new DecryptionException('Encrypted data does not appear to be formed properly');
			}
			
			if(!$this->isValidPayload($payload)) {
				throw new DecryptionException('Failed to decode encrypted data');
			}
			
			// decode iv
			$iv = base64_decode($payload['iv']);
			
			// decode and validate tag
			$tag = (!empty($payload['tag'])?base64_decode($payload['tag']):'');
			
			if(!$this->isValidTag($tag)) {
				throw new DecryptionException('Failed to decode tag');
			}
			
			// validate mac
			if(!$this->isValidMac($payload)) {
				throw new DecryptionException('Failed to validate mac');
			}
			
			// decrypt value
			$value = openssl_decrypt(
				$payload['value'],
				strtolower($this->cipher_method),
				$this->key,
				0,
				$iv,
				$tag
			);
			
			if(false === $value) {
				throw new DecryptionException('Failed to decrypt data');
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
				$this->digest_algo,
				$iv . $value,
				$this->key
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
		protected function isValidTag(string $tag): bool {
			// @TODO - validate length of tag based on cipher method
			
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