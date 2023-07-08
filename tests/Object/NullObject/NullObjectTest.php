<?php
	declare(strict_types=1);
	
	namespace Magnetar\Tests\Object\NullObject;
	
	use PHPUnit\Framework\TestCase;
	
	use Magnetar\Object\NullObject\NullObject;
	
	class NullObjectTest extends TestCase {
		protected ?NullObject $nullobject;
		
		protected function setUp(): void {
			$this->nullobject = new NullObject();
			
			parent::setUp();
		}
		
		public function testNonexistantProp() {
			$this->assertEmpty($this->nullobject->notset_val);
		}
		
		public function testGetProp() {
			$this->assertNull(
				$this->nullobject->testprop
			);
		}
		
		public function testIntProp() {
			$this->nullobject->intprop = 12345;
			
			$this->assertSame(12345, $this->nullobject->intprop);
		}
		
		public function testStringProp() {
			$this->nullobject->stringprop = 'Magnetar';
			
			$this->assertSame('Magnetar', $this->nullobject->stringprop);
		}
	}