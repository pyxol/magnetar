<?php
	declare(strict_types=1);
	
	namespace Magnetar\Please\Actions\Facades;
	
	use Exception;
	use ReflectionClass;
	use ReflectionMethod;
	use ReflectionParameter;
	
	use Magnetar\Please\Actionable;
	use Magnetar\Console\Output;
	use Magnetar\Please\Please;
	
	use Magnetar\Helpers\Facades\Log;
	
	/**
	 * An example Please action
	 */
	class PleaseActionDemo extends Actionable {
		/**
		 * {@inheritDoc}
		 */
		public function handle(
			Please $please
		): Output {
			Log::info('Demo action ran');
			
			return new Output('Demo ran successfully');
		}
	}