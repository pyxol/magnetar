<?php
	declare(strict_types=1);
	
	namespace Magnetar\Please\Actions\Facades;
	
	use Exception;
	use ReflectionClass;
	use ReflectionMethod;
	use ReflectionParameter;
	
	use Magnetar\Please\Actionable;
	use Magnetar\Console\Output;
	use Magnetar\Please\Please;
	
	use Magnetar\Helpers\Facades\Log;
	
	class UpdateFacadePHPDocs extends Actionable {
		/**
		 * {@inheritDoc}
		 */
		public function handle(
			Please $please
		): Output {
			$facade_dir = $please->getApp()->pathBase('src/Magnetar/Helpers/Facades/');
			
			// revert facade backups
			//$this->revertBackupFilenames($facade_dir);
			
			$facade_files = $this->getFacadeFiles($facade_dir);
			
			foreach($facade_files as $facade_filepath) {
				$facade = $this->processFacade($facade_filepath);
				
				Log::info($facade['name'] ." => ". $facade['class']);
				
				if(updateFacadePHPDoc($facade)) {
					Log::info('[[ SUCCESSFULLY UPDATED '. $facade['name'] .' ]]');
				} else {
					Log::error('[[ FAILED TO UPDATE '. $facade['name'] .' ]]');
				}
			}
			
			//$this->deleteFacadeBackupFiles($facade_dir);
			
			return new Output("Successfully updated PHPDocs for facades");
		}
		
		/**
		 * Get the Facade files
		 * @return array
		 */
		protected function getFacadeFiles(string $facades_folder_path): array {
			$facade_files = glob($facades_folder_path .'*.php');
			
			// filter out specific Facades
			$filter_out_facades = [
				'Facade',   // base Facade.php is not an actual Facade
			];
			
			$facade_files = array_filter($facade_files, function($facade_file) use ($filter_out_facades) {
				$facade_name = basename($facade_file, '.php');
				
				return !in_array($facade_name, $filter_out_facades);
			});
			
			return $facade_files;
		}
		
		/**
		 * Revert backup filenames for facades
		 * @param string $facade_folder_path The path to the Facade folder
		 * @return void
		 */
		protected function revertBackupFilenames(string $facade_folder_path): void {
			if(false === ($backup_files = glob($facade_folder_path .'*.php.bak'))) {
				Log::info('Did not find any backup facade files to revert');
			}
			
			foreach($backup_files as $backup_filepath) {
				Log::info('Working on '. $backup_filepath);
				
				$facade_filepath = preg_replace("#\.php\.bak$#si", '.php', $backup_filepath);
				
				// if backup file is the same as the facade file, skip it
				if($backup_filepath === $facade_filepath) {
					Log::warning('Facade file and backup file are the same: '. $facade_filepath);
					
					continue;
				}
				
				// delete facade file if it exists
				if(file_exists($facade_filepath)) {
					Log::info('Facade file exists, deleting: '. $facade_filepath);
					
					if(!unlink($facade_filepath)) {
						Log::error('Could not delete Facade file: '. $facade_filepath);
						
						continue;
					}
					
					clearstatcache(true, $facade_filepath);
				}
				
				// rename backup file to facade file
				rename($backup_filepath, $facade_filepath);
			}
		}
		
		/**
		 * Delete backup files for facades
		 * @param string $facade_folder_path The path to the Facade folder
		 * @return void
		 */
		protected function deleteFacadeBackupFiles(string $facade_folder_path): void {
			$backup_files = glob($facade_folder_path .'*.bak');
			
			if(empty($backup_files)) {
				Log::info("No backup files to delete.");
			}
			
			foreach($backup_files as $backup_filepath) {
				$facade_name = basename($backup_filepath, '.php.bak');
				
				print 'Working on '. $facade_name ."\n";
				
				// delete backup file
				if(!unlink($backup_filepath)) {
					Log::error('Could not delete backup file: '. $backup_filepath);
					
					continue;
				}
				
				Log::info('Deleted backup file for '. $facade_name .' facade');
				
				clearstatcache($backup_filepath);
			}
		}
		
		/**
		 * Convert a ReflectionMethod to a reference array
		 * @param ReflectionMethod $method The ReflectionMethod to convert
		 * @return array An assoc array of the method reference for internal use
		 */
		protected function convertReflectionMethodToReference(ReflectionMethod $method): array {
			if(preg_match('/\*\s*@method ([^\s]+) ([^\(]+)\(([^\)]*)\)/', $method->getDocComment(), $matches)) {
				$method_name = $matches[2];
				$method_params = $matches[3];
				$method_return_type = $matches[1];
				
				return [
					'name' => $method_name,
					'params' => $method_params,
					'return_type' => $method_return_type,
					//'doc' => $method->getDocComment(),
				];
			} else {
				$method_name = $method->getName();
				
				$method_params = $method->getParameters();
				
				$method_params = array_map(function(ReflectionParameter $param) {
					$param_txt = $param->getType() .' $'. $param->getName();
					
					if($param->isOptional()) {
						$param_txt .= '=';
						
						$value = $param->getDefaultValue();
						
						if(is_int($value) || is_float($value) || is_double($value) || is_long($value)) {
							$param_txt .= $value;
						} elseif(is_string($value)) {
							$param_txt .= ((false !== strpos($value, "'"))?'"'. $value .'"':"'". $value ."'");
						} elseif(is_bool($value)) {
							$param_txt .= ($value)?'true':'false';
						} elseif(null === $value) {
							$param_txt .= 'null';
						} elseif(is_array($value)) {
							$param_txt .= '[]';
						} elseif(is_object($value)) {
							$param_txt .= 'new '. get_class($value);
						} else {
							$param_txt .= $param->getDefaultValueConstantName() ?? '???';
						}
					}
					
					return $param_txt;
				}, $method_params);
				
				$method_return_type = (string)$method->getReturnType();
			}
			
			return [
				'method_def' => "@method ". $method_name ."(". implode(', ', $method_params) ."): ". $method_return_type .";",
				'class' => $method->getDeclaringClass()->getName(),
				'name' => $method_name,
				'params' => $method_params,
				'return_type' => $method_return_type,
				//'doc' => $method->getDocComment(),
			];
		}
		
		/**
		 * Process a Facade file
		 * @param string $facade_filepath The path to the Facade file
		 * @return array The processed Facade assoc array
		 * 
		 * @throws Exception If the Facade file does not exist
		 * @throws Exception If the Facade does not have a @see annotation
		 */
		protected function processFacade(string $facade_filepath): array {
			try {
				if(!file_exists($facade_filepath)) {
					throw new Exception("Facade file {$facade_filepath} does not exist.");
				}
				
				$facade_name = basename($facade_filepath, '.php');
				
				$contents = file_get_contents($facade_filepath);
				
				if(!preg_match('/@see ([A-Za-z0-9_\\\\]+)/', $contents, $matches) || ('' === ($referenced_class = trim($matches[1]))) || !class_exists($referenced_class)) {
					throw new Exception("Facade {$facade_name} does not have a @see annotation or referenced class does not exist.");
				}
				
				// get PHP method definitions for class
				$reflection = new ReflectionClass($referenced_class);
				
				// generate the method references array
				$reference_methods = [];
				
				// get method names
				$method_names = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
				
				foreach($method_names as $method) {
					$method_name = $method->getName();
					
					// filter out magic methods
					if(preg_match('/^__/', $method_name)) {
						continue;
					}
					
					$reference_methods[ $method_name ] = $this->convertReflectionMethodToReference($method);
				}
				
				// done
				return [
					'file' => $facade_filepath,
					'name' => $facade_name,
					'class' => $referenced_class,
					'methods' => $reference_methods,
				];
			} catch(Exception $e) {
				Log::error("[Exception]". $e->getMessage() ." (". $e->getFile() .":". $e->getLine() .")");
			}
		}
		
		/**
		 * Update the PHPDoc for a Facade
		 * @param array $facade The Facade to update
		 * @return bool True if the PHPDoc was updated, false otherwise
		 */
		protected function updateFacadePHPDoc(array $facade): bool {
			if(empty($facade['file']) || !file_exists($facade['file'])) {
				Log::error('Facade file for '. $facade['name'] .' does not exist');
				
				return false;
			}
			
			if(false === ($contents = file_get_contents($facade['file']))) {
				Log::error('Could not read '. $facade['name'] .' facade file');
				
				return false;
			}
			
			if(!preg_match("#/\*\*(.*?)\*/\s*class #si", $contents, $matches)) {
				Log::error('Could not find PHPDoc for '. $facade['name'] .' facade');
				
				return false;
			}
			
			$doc_lines = [
				"/**",
				
				implode("\n\t", array_map(function($method) {
					return " * ". $method['method_def'];
				}, $facade['methods'])),
				
				" * ",   // empty space between @methods and @see
				
				" * @see ". $facade['class'],
				" */",
			];
			
			$phpdoc = implode("\n\t", $doc_lines);
			
			Log::info("\tUpdating ". $facade['file'] .":");;
			//Log::info("\t". $phpdoc);;
			
			// make backup of facade file
			$backup_filepath = $facade['file'] .'.bak';
			
			if(!copy($facade['file'], $backup_filepath)) {
				Log::error('Could not create backup of facade file');
				
				return false;
			}
			
			// replace PHPDoc in facade file
			$contents = preg_replace("#/\*\*(?:.*?)\*/(\s*)class #si", $phpdoc ."\\1class ", $contents);
			
			// write new contents to facade file
			if(!file_put_contents($facade['file'], $contents)) {
				Log::error('Could not write new contents to facade file');
				
				return false;
			}
			
			Log::info("Successfully updated facade [". $facade['name'] ."]");
			
			return true;
		}
	}