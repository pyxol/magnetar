<?php
	declare(strict_types=1);
	
	namespace Magnetar\Tests\Utilities\Cryptography;
	
	use PHPUnit\Framework\TestCase;
	
	use Magnetar\Utilities\Cryptography\Scramble;
	
	class ScrambleTest extends TestCase {
		public function testEncryptionMatch(): void {
			$encoded_str1 = Scramble::encode('Magnetar');
			
			usleep(300);   // sleep for 300ms to ensure the time is different
			
			$encoded_str2 = Scramble::encode('Magnetar');
			
			$this->assertSame($encoded_str1, $encoded_str2);
		}
		
		public function testEncryptionDifferent(): void {
			$encoded_str1 = Scramble::encode('Magnetar');
			$encoded_str2 = Scramble::encode('Magnetar2');
			
			$this->assertNotSame($encoded_str1, $encoded_str2);
		}
		
		public function testDecryptionMatch(): void {
			$starting_str = "Magnetar";
			
			$encoded_str = Scramble::encode($starting_str);
			$decoded_str = Scramble::decode($encoded_str);
			
			$this->assertSame($starting_str, $decoded_str);
		}
		
		public function testDecryptionDifferent(): void {
			// 'twfNBMv0yxi' is from a previous run of Scramble::encode('Magnetar') should convert to 'Magnetar'
			$decoded_str = Scramble::decode('twfNBMv0yxi');
			
			$this->assertNotSame('Magnetar2', $decoded_str);
		}
	}