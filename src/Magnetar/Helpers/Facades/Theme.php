<?php
	declare(strict_types=1);
	
	namespace Magnetar\Helpers\Facades;
	
	use Magnetar\Helpers\Facades\Facade;
	
	/**
	 * @method theme(?string $theme_name=null): Magnetar\Template\Template;
	 * @method tpl(string $tpl_name, array $view_data=[]): string;
	 * 
	 * @see Magnetar\Template\ThemeManager
	 */
	class Theme extends Facade {
		/**
		 * Get the named key that this facade represents
		 * @return string
		 */
		protected static function getFacadeKey(): string {
			return 'theme';
		}
	}