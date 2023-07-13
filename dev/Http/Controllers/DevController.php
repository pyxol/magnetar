<?php
	declare(strict_types=1);
	
	use Magnetar\Http\Controller\Controller;
	use Magnetar\Helpers\Facades\App;
	use Magnetar\Helpers\Facades\DB;
	use Magnetar\Helpers\Facades\Response;
	
	class DevController extends Controller {
		public function index() {
			return Response::send('<html><head><title>Homepage</title><style>body { background-color: white; }</style></head><body>Hello, World!<br><a href="/db/">DB</a> | <a href="/dev/">Dev</a></body></html>');
		}
		
		public function db() {
			// in lieu of service workers
			App::bind('database', fn () => new Magnetar\Database\ConnectionManager(App::getInstance()));
			
			// list tables
			$tables = DB::get_rows("
				SHOW TABLES
			");
			
			Response::send('<pre>'. esc_html(print_r($tables, true)) .'</pre>');
		}
		
		public function devpage() {
			Response::send('Dev page');
		}
	}