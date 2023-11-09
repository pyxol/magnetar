<?php
	declare(strict_types=1);
	
	namespace Magnetar\Helpers\Facades;
	
	use Magnetar\Helpers\Facades\Facade;
	
	/**
	 * @method theme(?string $theme_name=null): Magnetar\Template\Template
	 * @method tpl(string $tpl_name, array $view_data=[]): string
	 * @method getViewPath(string $tpl_name): string
	 * @method render(string $tpl_name, array $view_data=[]): string
	 * @method display(string $tpl_name): void
	 * @method renderResponse(string $tpl_name, array $view_data=[]): Magnetar\Http\Response
	 * @method getData(): array
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