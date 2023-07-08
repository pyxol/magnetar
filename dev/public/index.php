<?php
	// rudimentary script to test the framework
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
	
	require_once(__DIR__ ."/../../vendor/autoload.php");
	
	use Magnetar\Application;
	use Magnetar\Kernel\Http\Kernel;
	use Magnetar\Http\Controller\Controller;
	use Magnetar\Http\Request\Request;
	use Magnetar\Http\Response\Response;
	
	use Magnetar\Helpers\Facades\Config;
	use Magnetar\Helpers\Facades\DB;
	
	// test-specific stuff
	try {
		// initialize whoops
		$whoops = new Whoops\Run;
		$whoops->pushHandler(new Whoops\Handler\PrettyPageHandler);
		$whoops->register();
	} catch(Exception $e) {
		die("Error initializing Whoops: ". $e->getMessage());
	}
	
	class DevController extends Controller {
		public function index(Request $req, Response $res) {
			$tables = DB::get_rows("
				SHOW TABLES
			");
			
			$res->send('Hello, World!');
		}
		
		public function db(Request $req, Response $res) {
			$tables = DB::get_rows("
				SHOW TABLES
			");
			
			//$tables = Config::get('database.tables');
			
			$res->send('<pre>'. esc_html(print_r($tables, true)) .'</pre>');
		}
		
		public function devpage(Request $req, Response $res) {
			$res->send('Dev page');
		}
	}
	// end of test-specific stuff
	
	try {
		$app = new Application(
			dirname(__DIR__)
		);
		
		$app->singleton(Kernel::class, function($app) {
			return new Kernel($app);
		});
		
		$kernel = $app->make(Kernel::class);
		
		// routes
		$kernel->get(
			'/^dev\/?/i',
			[DevController::class, 'devpage']
		);
		
		$kernel->get(
			'/^db\/?/i',
			[DevController::class, 'db']
		);
		
		$kernel->get(
			'/^\/?/i',
			[DevController::class, 'index']
		);
		
		$kernel->serve();
	} catch(Exception $e) {
		print "Error: ". $e->getMessage();
	}