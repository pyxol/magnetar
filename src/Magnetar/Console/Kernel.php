<?php
	declare(strict_types=1);
	
	namespace Magnetar\Console;
	
	use Magnetar\Application;
	use Magnetar\Console\Input;
	
	class Kernel {
		/**
		 * Middleware stack
		 * @var array
		 */
		protected array $middleware = [];
		
		/**
		 * Array of classes to instantiate and call using kernel->bootstrap()
		 * @var array
		 */
		protected array $bootstrappers = [
			\Magnetar\Bootstrap\LoadConfigs::class,
			\Magnetar\Bootstrap\RegisterFacades::class,
			
			// @TODO console-specific bootstrappers
			
			\Magnetar\Bootstrap\RegisterServiceProviders::class,
			\Magnetar\Bootstrap\BootServiceProviders::class,
		];
		
		/**
		 * Constructor
		 * @param Application $app The application instance
		 */
		public function __construct(
			protected Application $app
		) {
			
		}
		
		/**
		 * Handle a console command
		 * @param Input $input The input object
		 * @param Output $output The output object
		 * @return int The exit status
		 */
		public function handle(
			Input $input,
			Output $output
		): int {
			// @TODO take input, route and execute, get and return the exit status
			
			return 0;
		}
		
		/**
		 * Terminate the console command
		 * @param Input $input The input object
		 * @param int $status The exit status
		 * @return void
		 */
		public function terminate(
			Input $input,
			int $status
		) {
			$this->app->terminate();
		}
		
		
		
		
		
	}