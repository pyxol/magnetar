<?php
	declare(strict_types=1);
	
	use Magnetar\Helpers\Env;
	use Magnetar\Helpers\Facades\Theme;
	
	if(!function_exists('env')) {
		/**
		 * Get an environment variable
		 * @param string $key
		 * @param mixed $default
		 * @return mixed
		 */
		function env(string $key, mixed $default): mixed {
			return Env::get($key, $default);
		}
	}
	
	if(!function_exists('tpl')) {
		/**
		 * Render a template from the active theme
		 * @param string $template_name Template name
		 * @param mixed $data Optional. Data to pass to the template file
		 * @return mixed
		 */
		function tpl(string $template_name, mixed $data=[]): mixed {
			return Theme::tpl($template_name, $data);
		}
	}
	
	if(!function_exists('display_tpl')) {
		/**
		 * Render a template from the active theme
		 * @param string $template_name Template name
		 * @param mixed $data Optional. Data to pass to the template file
		 * @return void
		 */
		function display_tpl(string $template_name, mixed $data=[]): void {
			print Theme::tpl($template_name, $data);
		}
	}