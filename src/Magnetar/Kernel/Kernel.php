<?php
	declare(strict_types=1);
	
	namespace Magnetar\Kernel;

	use Magnetar\Kernel\AbstractKernel;
	use Magnetar\Application;
	use Magnetar\Kernel\KernelPanicException;
	
	// raw kernel class
	class Kernel extends AbstractKernel {
		protected Application $app;
		
		protected array $bootstrappers = [
			\Magnetar\Bootstrap\LoadConfigs::class,
		];
		
		public function __construct(
			Application $app
		) {
			$this->app = $app;
		}
		
		/**
		 * Initialize method called by constructor
		 * @return void
		 */
		protected function preprocess(): void {
			// do nothing
		}
		
		/**
		 * Called after kernel execution
		 * @return void
		 */
		protected function postprocess(): void {
			// do nothing
		}
		
		/**
		 * Handle kernel panic
		 * @param KernelPanicException $e
		 * @return void
		 */
		protected function handlePanic(KernelPanicException $e): void {
			// print out message from caught exception and exit
			die($e->getMessage());
		}
	}