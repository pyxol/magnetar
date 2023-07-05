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
	
	// test-specific stuff
	try {
		// initialize whoops
		$whoops = new Whoops\Run;
		$whoops->pushHandler(new Whoops\Handler\PrettyPageHandler);
		$whoops->register();
	} catch(Exception $e) {
		die("Error initializing Whoops: ". $e->getMessage());
	}
	
	class TestController extends Controller {
		public function index(Request $req, Response $res) {
			$res->send('Hello, World!');
		}
		
		public function test(Request $req, Response $res) {
			$res->send('Test page');
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
			'/^test\/?/i',
			[TestController::class, 'test']
		);
		
		$kernel->get(
			'/^\/?/i',
			[TestController::class, 'index']
		);
		
		$kernel->serve();
	} catch(Exception $e) {
		print "Error: ". $e->getMessage();
	}