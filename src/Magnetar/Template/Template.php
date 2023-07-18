<?php
	declare(strict_types=1);
	
	namespace Magnetar\Template;
	
	use Magnetar\Application;
	
	class Template {
		protected Data $data;
		
		protected string $folder = "";
		protected string $base_path = "";
		
		/**
		 * Template constructor.
		 * @param string $template_folder The folder to load templates from
		 * @param string $base_template_path The base path to the template folder
		 */
		public function __construct(
			protected Application $app,
			string $template_folder
		) {
			// assign paths
			
			
			$this->folder = $template_folder;
			$this->base_path = $this->app->basePath(
				$this->app->make('config')->get('theme.storage.base_path', 'themes')
				.'/'. $this->folder .'/'
			);
			
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
			if(!preg_match("#\.php$#si", $tpl_name)) {
				$tpl_name .= '.php';
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