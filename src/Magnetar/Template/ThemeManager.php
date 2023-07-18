<?php
	declare(strict_types=1);
	
	namespace Magnetar\Template;
	
	use Exception;
	
	use Magnetar\Application;
	use Magnetar\Template\Template;
	
	class ThemeManager {
		protected array $themes = [];
		
		public function __construct(
			protected Application $app
		) {
			
		}
		
		/**
		 * Returns the active template for the specified theme
		 * @param string|null $theme_name
		 * @return AbstractTheme
		 * 
		 * @throws Exception
		 */
		public function theme(string|null $theme_name=null): Template {
			if(is_null($theme_name)) {
				if(null === ($theme_name = $this->app->make('config')->get('theme.default', null))) {
					throw new Exception("No default theme folder name specified");
				}
			}
			
			if(isset($this->themes[ $theme_name ])) {
				return $this->themes[ $theme_name ];
			}
			
			return $this->themes[ $theme_name ] = new Template(
				$this->app,
				$theme_name
			);
		}
		
		/**
		 * Returns a rendered theme template file
		 * @param string $tpl_name Template name (with or without extension)
		 * @param array $view_data Optional. Data to pass to the template file
		 * @return void
		 */
		public function tpl(string $tpl_name, array $view_data=[]): string {
			return $this->theme()->render($tpl_name, $view_data);
		}
		
		/**
		 * Passes method calls to the active cache store
		 * @param string $method
		 * @param array $args
		 * @return void
		 */
		public function __call(string $method, array $args) {
			return $this->theme()->$method(...$args);
		}
	}