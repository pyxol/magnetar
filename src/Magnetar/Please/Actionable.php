<?php
	declare(strict_types=1);
	
	namespace Magnetar\Please;
	
	use Magnetar\Please\AbstractActionable;
	use Magnetar\Please\Please;
	use Magnetar\Console\Output;
	use Magnetar\Console\ErrorOutput;
	
	class Actionable extends AbstractActionable {
		/**
		 * {@inheritDoc}
		 */
		public function handle(
			Please $please
		): Output {
			return new ErrorOutput('Please implement the handle method');
		}
	}