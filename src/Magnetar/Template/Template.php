<?php
	declare(strict_types=1);
	
	namespace Magnetar\Template;
	
	use Magnetar\Template\Data;
	use Magnetar\Template\View;
	
	class Template {
		protected Data $data;
		
		protected string $folder = "";
		protected string $base_path = "";
		
		/**
		 * Template constructor.
		 * @param string $template_folder The folder to load templates from
		 * @param string $base_template_path The base path to the template folder
		 */
		public function __construct(string $template_folder=DEFAULT_PUBLIC_TEMPLATE, string $base_template_path=TEMPLATE_DIR) {
			// bootstrap template utilities
			//require_once(__DIR__ ."/globals.php");
			// ^-- should be autoloaded by composer
			
			// assign paths
			$this->folder = $template_folder;
			$this->base_path = $base_template_path . $this->folder .'/';
			
			// initialize the template data
			$this->data = new Data();
		}
		
		/**
		 * Get the path to a template file
		 * @param string $tpl_name The template name to get the path for
		 * @return string
		 */
		public function getPath(string $tpl_name): string {
			// sanitize the template name
			if(false === strpos($tpl_name, ".php")) {
				$tpl_name .= ".php";
			}
			
			return $this->base_path . $tpl_name;
		}
		
		/**
		 * Return a rendered template
		 * @param string $tpl_name The template name to render
		 * @param array $data The data to pass to the template
		 * @return string
		 */
		public function render(string $tpl_name, array $view_data=[]): string {
			// create a TemplateView instance
			$tpl_view = new View($this, $this->getPath($tpl_name), $view_data);
			
			// render the template and return
			return $tpl_view->render();
		}
		
		/**
		 * Render and template and print results
		 * @param string $tpl_name The template name to render
		 * @return void
		 */
		public function display(string $tpl_name): void {
			print $this->render($tpl_name);
		}
		
		/**
		 * Get a template variable
		 * @param string $name
		 * @param mixed|null $default
		 * @return mixed
		 */
		public function __get(string $name): mixed {
			return $this->data->$name ?? null;
		}
		
		/**
		 * Set a template variable
		 * @param string $name
		 * @param mixed $value
		 * @return void
		 */
		public function __set(string $name, mixed $value): void {
			$this->data->$name = $value;
		}
		
		/**
		 * Check if a template variable is set
		 * @param string $name
		 * @return bool
		 */
		public function __isset(string $name): bool {
			return isset($this->data->$name);
		}
		
		/**
		 * Unset a template variable
		 * @param string $name
		 * @return void
		 */
		public function __unset(string $name): void {
			unset($this->data->$name);
		}
		
		/**
		 * Get the template data
		 * @return array
		 */
		public function getData(): array {
			return $this->data->toArray();
		}
	}