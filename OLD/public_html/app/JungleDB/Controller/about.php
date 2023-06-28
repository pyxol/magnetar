<?php
	namespace JungleDB;
	
	use \api as api;
	
	class Controller_about extends Abstract_Controller {
		public function get($page="index") {
			api::tpl()->view("about/". $page);
		}
	}