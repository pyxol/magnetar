<?php
	declare(strict_types=1);
	
	namespace Magnetar\Http\Controller;
	
	use Magnetar\Kernel\Kernel;
	
	class Controller {
		protected Kernel $app;
		
		// @TODO needs more work
		public function __construct(Kernel $kernel) {
			// assign app
			$this->app = $kernel;
		}
	}