<?php
	declare(strict_types=1);
	
	namespace Magnetar\Template;
	
	use Magnetar\Template\Template;
	
	// a class to encapsulate a template that's being rendered
	// holds data via magic methods
	class View {
		protected Template $template;
		protected string $template_path;
		
		/**
		 * Data stored specifically in this View, overrides data from the Template
		 * @var array
		 */
		protected array $data = [];
		
		/**
		 * TemplateView constructor.
		 * @param Template $template The template instance
		 * @param string $template_path The path to the template file
		 * @param array $view_data The data to pass to the template
		 */
		public function __construct(Template $template, string $template_path, array $view_data=[]) {
			//print "data=". print_r($view_data, true) ."<br>";
			
			$this->template = $template;
			$this->template_path = $template_path;
			
			// set the view data
			$this->data = array_merge($this->template->getData(), $view_data);
		}
		
		/**
		 * Render the template
		 * @return string
		 */
		public function render(): string {
			// render the template
			ob_start();
			
			include($this->template_path);
			
			return ob_get_clean();
		}
		
		/**
		 * Render a template view
		 * @param string $tpl_name The template name to render
		 * @param array $data The data to pass to the template
		 * @return string
		 */
		public function renderView(string $tpl_name, array $view_data=[]): string {
			return $this->template->render($tpl_name, array_merge($this->data, $view_data));
		}
		
		/**
		 * Display a template view
		 * @param string $tpl_name The template name to render
		 * @param array $data The data to pass to the template
		 * @return void
		 */
		public function view(string $tpl_name, array $view_data=[]): void {
			print $this->renderView($tpl_name, $view_data);
		}
		
		/**
		 * Get a template view variable. If not set, attempts to return the variable from the template
		 * @param string $name
		 * @param mixed|null $default
		 * @return mixed
		 */
		public function __get(string $name): mixed {
			return $this->data[ $name ] ?? null;
			
			//if(isset($this->data[ $name])) {
			//	return $this->data[ $name];
			//}
			//
			//return $this->template->data->$name ?? null;
		}
		
		/**
		 * Set a template view variable. Does not set the variable in the template
		 * @param string $name
		 * @param mixed $value
		 * @return void
		 */
		public function __set(string $name, mixed $value): void {
			$this->data[ $name ] = $value;
			//$this->template->data->$name = $value;
		}
		
		/**
		 * Check if a template view variable is set. If not, checks if the template has the variable
		 * @param string $name
		 * @return bool
		 */
		public function __isset(string $name): bool {
			//// first check if this view has the variable
			//if(isset($this->data[ $name ])) {
			//	return true;
			//}
			//
			//// check if the template has the variable
			//return isset($this->template->data->$name);
			
			return isset($this->data[ $name ]);
		}
		
		/**
		 * Unset a view variable
		 * @param string $name
		 * @return void
		 */
		public function __unset(string $name): void {
			unset($this->data[ $name ]);
		}
		
		
		public function debug__getData(): array {
			return $this->data;
		}
	}