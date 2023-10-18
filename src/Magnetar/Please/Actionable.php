<?php
	declare(strict_types=1);
	
	namespace Magnetar\Please;
	
	use Magnetar\Please\Please;
	use Magnetar\Console\Output;
	use Magnetar\Console\ErrorOutput;
	
	class Actionable {
		/**
		 * Handle the action
		 * @param Please $please The Please instance
		 * @return Output
		 */
		public function handle(
			Please $please
		): Output {
			return new ErrorOutput('The actionable called does not provide a handle method');
		}
	}