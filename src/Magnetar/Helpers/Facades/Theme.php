<?php
	declare(strict_types=1);
	
	namespace Magnetar\Helpers\Facades;
	
	use Magnetar\Helpers\Facades\Facade;
	
	/**
	 * @method static \Magnetar\Template\Template theme(?string $theme_name)
	 * @method static string tpl(string $tpl_name, array $view_data)
	 * @method static string getViewPath(string $tpl_name)
	 * @method static string render(string $tpl_name, array $view_data)
	 * @method static void display(string $tpl_name)
	 * @method static \Magnetar\Http\Response renderResponse(string $tpl_name, array $view_data)
	 * @method static array getData()
	 * 
	 * @see \Magnetar\Template\ThemeManager
	 * @see \Magnetar\Template\Template
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