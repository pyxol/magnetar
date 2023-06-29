<?php
	declare(strict_types=1);
	
	namespace Magnetar\Filesystem\Adapter;
	
	interface AdapterInterface {
		public function path(string $path): string;
	}