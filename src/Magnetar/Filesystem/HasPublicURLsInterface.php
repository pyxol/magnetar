<?php
	declare(strict_types=1);
	
	namespace Magnetar\Filesystem;
	
	use Magnetar\Filesystem\Exceptions\NoPublicEndpointException;
	
	/**
	 * Shapes a disk adapter to have the ability to manage files
	 */
	interface HasPublicURLsInterface {
		/**
		 * Generate a public URL for a file path
		 * @param string $rel_path File path
		 * @return string Public URL
		 * 
		 * @throws NoPublicEndpointException
		 */
		public function url(
			string $rel_path
		): string;
	}