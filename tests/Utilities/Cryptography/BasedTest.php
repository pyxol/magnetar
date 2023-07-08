<?php
	declare(strict_types=1);
	
	namespace Magnetar\Tests\Utilities\Cryptography;
	
	use PHPUnit\Framework\TestCase;
	
	use Magnetar\Utilities\Cryptography\Based;
	
	class BasedTest extends TestCase {
		public function testEncryptionMatch() {
			$encoded_str1 = Based::encode('Magnetar');
			
			usleep(300);   // sleep for 300ms to ensure the time is different
			
			$encoded_str2 = Based::encode('Magnetar');
			
			$this->assertSame($encoded_str1, $encoded_str2);
		}
		
		public function testEncryptionDifferent() {
			$encoded_str1 = Based::encode('Magnetar');
			$encoded_str2 = Based::encode('Magnetar2');
			
			$this->assertNotSame($encoded_str1, $encoded_str2);
		}
		
		public function testDecryptionMatch() {
			$starting_str = "Magnetar";
			
			$encoded_str = Based::encode($starting_str);
			$decoded_str = Based::decode($encoded_str);
			
			$this->assertSame($starting_str, $decoded_str);
		}
		
		public function testDecryptionDifferent() {
			// 'twfNBMv0yxi' is from a previous run of Based::encode('Magnetar') should convert to 'Magnetar'
			$decoded_str = Based::decode('twfNBMv0yxi');
			
			$this->assertNotSame('Magnetar2', $decoded_str);
		}
	}