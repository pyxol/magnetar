<?php
	declare(strict_types=1);
	
	namespace Magnetar\Please;
	
	use Magnetar\Console\Output;
	
	abstract class AbstractActionable {
		/**
		 * Handle the action
		 * @param Please $please The Please instance
		 * @return Output
		 */
		abstract public function handle(
			Please $please
		): Output;
	}