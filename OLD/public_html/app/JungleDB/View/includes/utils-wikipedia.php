<?php
	// Wikipedia
	
	function pullAndParse_wikipediaById($id, $parse_options = array()) {
		global $site;
		global $db;
		
		require_once(DIR_BASE ."dev". DIRECTORY_SEPARATOR ."jungle_wikipedia_parser". DIRECTORY_SEPARATOR ."wiki_parser.php");
		
		$db->select_db(WIKIPEDIA_DATABASE);
		$wikipedia = $db->get_row("SELECT * FROM `articles` WHERE `wiki_id` = '". $db->escape($id) ."'");
		$db->select_db(DB_NAME);
		
		if(empty($wikipedia)) {
			return false;
		}
		
		//print "<pre>". print_r($wikipedia,1) ."</pre>\n";
		
		// extract info
		$file_name = WIKIPEDIA_DIR_FILES . implode("/", str_split(str_pad($id, 9, "0", STR_PAD_LEFT), 3)) .".txt";
		
		if(!file_exists($file_name)) {
			return false;
		}
		
		$contents = file_get_contents($file_name);
		
		$parser = new Jungle_WikiSyntax_Parser($contents, $wikipedia['title']);
		//$parsed = $parser->parse();
		return $parser->parse($parse_options);
	}
	
	
	
	function jungleWiki($syntax, $extra_stuff=true) {
		$lines = explode(PHP_EOL, $syntax);
		
		if(!empty($extra_stuff)) {
			$list_open = false;
			
			foreach(array_keys($lines) as $key) {
				$lines[$key] = trim($lines[$key]);
				
				if(!preg_match("#^\s*(?:\*|\#)\s*(.+)\s*$#si", $lines[$key], $match)) {
					if($list_open === true) {
						$lines[$key] = "</ul>\n". $lines[$key];
						
						$list_open = false;
					}
					
					continue;
				}
				
				$lines[$key] = "<li>". $match[1] ."</li>";
				
				if($list_open === false) {
					$lines[$key] = "<ul class=\"wiki_inline_list\">". $lines[$key];
					
					$list_open = true;
				}
			}
			
			if($list_open === true) {
				array_push($lines, "</ul>");
			}
		} else {
			foreach(array_keys($lines) as $key) {
				$lines[$key] = trim(preg_replace("#^\s*([\#\:\;\*]{1,})\s*#i", "", $lines[$key]));
			}
		}
		
		$syntax = implode(PHP_EOL, $lines);
		
		$syntax = preg_replace_callback("#\[wiki\=([a-f0-9]{32})(\#[^\]]+)?\]([^\]]+?)\[\/wiki\]#si", function($match) {
			return "<a href=\"/wiki_hash/". $match[1] . $match[2] ."/\" class=\"wiki_inline_link\" title=\"Wikipedia Entry for ". esc_attr($match[3]) ."\">". $match[3] ."</a>";
		}, $syntax);
		
		$syntax = trim($syntax);
		
		return $syntax;
	}
	
	
	function parse_wiki_syntax_section($section, $depth=1) {
		if(empty($section['text']) && empty($section['children'])) {
			return;
		}
		
		if($depth > 1) {
			print "<div style=\"border-left: 2px solid #f0f0f0; margin-left: 10px; padding-left: 10px;\">\n";
		}
		
		if(!empty($section['title'])) {
			print "<h". ($depth + 1) ." style=\"margin-bottom: 5px; font-weight: bold; font-size: ". (($depth > 1)?(18 - $depth*2):"18") ."px;\">". jungleWiki($section['title']) ."</h". ($depth + 1) .">\n";
		}
		
		if(!empty($section['text'])) {
			$parsed_wiki_section_texts = preg_split("#". PHP_EOL ."{2,}#si", jungleWiki($section['text']));
			
			if(!empty($parsed_wiki_section_texts)) {
				foreach($parsed_wiki_section_texts as $parsed_wiki_section_text) {
					if(!preg_match("#^<ul(?:.+?)/ul>$#si", $parsed_wiki_section_text)) {
						print "<p class=\"wiki_text_paragraph\">". $parsed_wiki_section_text ."</p>";
					} else {
						print $parsed_wiki_section_text;
					}
					
					print PHP_EOL . PHP_EOL;
				}
			}
			
			//print "<p class=\"wiki_text_pharagraph\">". preg_replace("#". PHP_EOL ."{2,}#si", "</p>\n<p class=\"wiki_text_pharagraph\">", jungleWiki($section['text'])) ."</p>\n";
		}
		
		if(!empty($section['children'])) {
			foreach($section['children'] as $child) {
				parse_wiki_syntax_section($child, ($depth + 1));
			}
		}
		
		if($depth > 1) {
			print "</div>\n";
		}
	}
	
	function wiki_displayTable($table, $with_headings=false) {
		if(empty($table['data'])) {
			return;
		}
		
		if(!empty($with_headings)): ?><h3 class="section_title"><?=html_entity_decode($table['section'], ENT_COMPAT, 'UTF-8');?></h3><?php endif;
		
		?>
		<table class="wiki_table">
			<?php
				$in_thead = false;
				
				foreach($table['data'] as $row) {
					if($row['type'] === "header" && $in_thead === false) {
						$in_thead = true;
						
						print "	<thead>\n";
					}
					
					if($row['type'] === "data" && $in_thead === true) {
						$in_thead = false;
						
						print "	</thead>\n";
						print "	<tbody>\n";
					}
					
					print "		<tr>\n";
					
					foreach($row['columns'] as $column) {
						print "			<td";
						
						if(!empty($column['rowspan'])) {
							print " valign=\"center\" rowspan=\"". esc_attr($column['rowspan']) ."\"";
						}
						
						if(!empty($column['colspan'])) {
							print " align=\"center\" colspan=\"". esc_attr($column['colspan']) ."\"";
						}
						
						print ">";
						
						$column_text = $column['text'];
						$column_text = jungleWiki($column_text);
						$column_text = preg_replace("#(". PHP_EOL ."|<br(\s*\/\s*)?>){1,}#si", "<br /><br />", $column_text);
						
						print $column_text;
						
						print "</td>\n";
					}
					
					print "		</tr>\n";
				}
			?>
			</tbody>
		</table>
		<?php
	}