<?php
	declare(strict_types=1);
	
	namespace Magnetar\Tests\Util\Cryptography;
	
	use PHPUnit\Framework\TestCase;
	
	use Magnetar\Util\Cryptography\Encryption;
	
	class EncryptionTest extends TestCase {
		public function testDefaultCryptography() {
			$encryption_engine = new Encryption(
				md5('another_random_salt_'. microtime(true))
			);
			
			$starting_str = 'Magnetar';
			
			// encrypt
			$encrypted_str = $encryption_engine->encrypt($starting_str);
			
			// decrypt
			$decrypted_str = $encryption_engine->decrypt($encrypted_str);
			
			$this->assertSame($starting_str, $decrypted_str);
		}
		
		public function testSpecificCryptography() {
			$encryption_engine = new Encryption(
				'salt_'. time(),
				'SHA256',
				'aes-128-ctr'
			);
			
			$starting_str = 'Magnetar';
			
			// encrypt
			$encrypted_str = $encryption_engine->encrypt($starting_str);
			
			// decrypt
			$decrypted_str = $encryption_engine->decrypt($encrypted_str);
			
			$this->assertSame($starting_str, $decrypted_str);
		}
		
		public function testMultipleEncryptions() {
			$encryption_engine = new Encryption(
				md5('random_salt_'. microtime(true))
			);
			
			$raw_str = 'Magnetar';
			
			$encrypted_str1 = $encryption_engine->encrypt($raw_str);
			
			usleep(300);   // sleep for 300ms to ensure the time is different
			
			$encrypted_str2 = $encryption_engine->encrypt($raw_str);
			
			$this->assertNotFalse($encrypted_str1);
			
			$this->assertNotFalse($encrypted_str2);
		}
		
		public function testMultipleDecryptions() {
			$encryption_engine = new Encryption(
				md5('random_salt_'. microtime(true))
			);
			
			$raw_str = 'Magnetar';
			
			$encrypted_str = $encryption_engine->encrypt($raw_str);
			
			$decrypted_str1 = $encryption_engine->decrypt($encrypted_str);
			
			usleep(300);   // sleep for 300ms to ensure the time is different
			
			$decrypted_str2 = $encryption_engine->decrypt($encrypted_str);
			
			$this->assertNotFalse($encrypted_str);
			
			$this->assertNotFalse($decrypted_str1);
			$this->assertNotFalse($decrypted_str2);
			
			$this->assertSame($decrypted_str1, $decrypted_str2);
		}
		
		public function testDifferentEncryptedValues() {
			$encryption_engine = new Encryption(
				md5('random_salt_'. microtime(true))
			);
			
			$encrypted_str1 = $encryption_engine->encrypt('Magnetar');
			$decrypted_str1 = $encryption_engine->decrypt($encrypted_str1);
			
			
			$encrypted_str2 = $encryption_engine->encrypt('Magnetar2');
			$decrypted_str2 = $encryption_engine->decrypt($encrypted_str2);
			
			$this->assertNotFalse($encrypted_str1);
			$this->assertNotFalse($decrypted_str1);
			
			$this->assertNotFalse($encrypted_str2);
			$this->assertNotFalse($decrypted_str2);
			
			$this->assertNotSame($decrypted_str1, $decrypted_str2);
		}
	}