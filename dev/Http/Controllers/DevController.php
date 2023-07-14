<?php
	declare(strict_types=1);
	
	use Magnetar\Http\Controller\Controller;
	use Magnetar\Helpers\Facades\App;
	use Magnetar\Helpers\Facades\DB;
	use Magnetar\Helpers\Facades\Response;
	use Magnetar\Helpers\Facades\Cache;
	use Magnetar\Helpers\Facades\Log;
	
	class DevController extends Controller {
		public function index() {
			return Response::send('
			<html>
			<head>
			<title>Homepage</title>
			<style>
			body { background-color: white; }
			</style>
			</head>
			<body>
			<p>Hello, World!</p>
			<hr>
			<a href="/db/">DB</a> | <a href="/set_cache/">Set Cache</a> | <a href="/get_cache/">Get Cache</a>
			</body>
			</html>');
		}
		
		public function db() {
			// in lieu of service providers
			App::bind('database', fn () => new Magnetar\Database\ConnectionManager(App::getInstance()));
			
			// list tables
			$tables = DB::get_rows("
				SHOW TABLES
			");
			
			Response::send('<pre>'. esc_html(print_r($tables, true)) .'</pre>');
		}
		
		public function set_cache() {
			// in lieu of service providers
			App::bind('cache', fn () => new Magnetar\Cache\StoreManager(App::getInstance()));
			App::bind('logger', fn () => new Magnetar\Log\Logger(App::getInstance()));
			
			$cached_val = date('r');
			$cache_set = Cache::set('cached_val', $cached_val, 15);
			
			Response::send('Set Cache: '. $cached_val .' | <a href="/get_cache/">Get Cache</a><hr>'. Log::dump(0, true));
		}
		
		public function get_cache() {
			// in lieu of service providers
			App::bind('cache', fn () => new Magnetar\Cache\StoreManager(App::getInstance()));
			App::bind('logger', fn () => new Magnetar\Log\Logger(App::getInstance()));
			
			$cached_val = Cache::get('cached_val') ?? "NOT SET";
			
			Response::send('Get Cache: '. $cached_val .' | <a href="/get_cache/">Refresh</a> | <a href="/set_cache/">Set Cache</a><hr>'. Log::dump(0, true));
		}
	}