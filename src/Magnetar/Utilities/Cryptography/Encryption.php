<?php
	declare(strict_types=1);
	
	namespace Magnetar\Utilities\Cryptography;
	
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
			if(null !== $digest_method) {
				$this->digest_method = $digest_method;
			}
			
			if(null !== $cipher_method) {
				$this->cipher_method = $cipher_method;
			}
		}
		
		/**
		 * Encrypt a string with config-based key/settings that can passed on and be decrypted via Encryption->decrypt. Returns false on error (usually from bad digest_method/cipher_method settings)
		 * @param string $raw_string String to encrypt
		 * @return string|false
		 */
		public function encrypt(string $raw_string): string|false {
			$enc_key = openssl_digest($this->salt, $this->digest_method, true);
			$enc_iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($this->cipher_method));
			
			return openssl_encrypt(
				$raw_string,
				$this->cipher_method,
				$enc_key,
				0,
				$enc_iv
			) .'::'. bin2hex($enc_iv);
		}
		
		/**
		 * Decrypt a previously encrypted string from Encryption->encrypt. Returns false on error (mostly from wrong encrypted data or a changed encryption key, or sometimes from bad digest_method/cipher_method settings)
		 * @param string $crypted_string String to decrypt
		 * @return string|false
		 */
		public function decrypt(string $crypted_string): string|false {
			list($crypted_token, $enc_iv) = explode('::', $crypted_string, 2);
			$enc_key = openssl_digest($this->salt, $this->digest_method, true);
			
			return openssl_decrypt(
				$crypted_token,
				$this->cipher_method,
				$enc_key,
				0,
				hex2bin($enc_iv)
			);
		}
	}