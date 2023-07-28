<?php
	declare(strict_types=1);
	
	namespace App\ServiceProviders;
	
	use Magnetar\Helpers\ServiceProvider;
	
	class AppServiceProvider extends ServiceProvider {
		/**
		 * Register application-specific services
		 *
		 * @return void
		 */
		public function register(): void {
			//
		}
		
		/**
		 * Bootstrap application-specific services
		 *
		 * @return void
		 */
		//public function boot(): void {
		//	// @TODO boot() method doesn't seem to work yet
		//}
	}