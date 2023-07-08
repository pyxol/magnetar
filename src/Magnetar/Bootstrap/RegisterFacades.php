<?php
	declare(strict_types=1);
	
	namespace Magnetar\Bootstrap;
	
	use Magnetar\Application;
	use Magnetar\Helpers\Facades\Facade;
	use Magnetar\Helpers\AliasLoader;
	
	class RegisterFacades implements BootstrapLoaderInterface {
		public function bootstrap(Application $app): void {
			Facade::clearResolvedInstances();   // reset any resolved instances
			
			Facade::setFacadeApplication($app);
			
			// register app aliases
			//$aliases = $app->make('config')->get('app.aliases', []);
			
			/* foreach($aliases as $alias => $class) {
				//$app->alias($alias, $class);
				print "Registering alias: $alias => $class<br>\n";
				$app->bind($alias, $class);
			} */
			
			// register facades
			$aliases = $app->make('config')->get('app.aliases', []);
			
			//die("aliases = <pre>". esc_html(print_r($aliases, true)) ."</pre>");
			AliasLoader::getInstance($aliases)->register();
		}
	}