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
		
		$parser_options = array();
		
		$parser_options['ignore_template_matches'] = array(
			 "#(Wikiquote|Use mdy dates|nowrap|convert|clear)#si"   // functions on words
			,"#^(link fa|good article|anchor|commons|reflist|refimprove|refbegin|refend|Unreferenced|Citation needed|Primary sources|Citation style|medref|no footnotes|more footnotes|cleanup\-|Sources|Verification|Verify)#si"   // wikipedia-specific structurals
			,"#^(X mark|Check mark|Tick|hmmm|n\.b\.|bang|(N|Y)\&|Ya|Y|aye|Check mark\-n|X mark\-n|X mark big|Cross((?:\s*)\|(?:[0-9]+?)(?:\s*?)))$#si"   // checkboxes
			,"#^(Empty section|Wikinews|link recovered via|expandsect|sortname|see also|sfn|also|Subscription required|Fact|Full|Page needed|Season needed|Volume needed|Clarify|Examples|List fact|Nonspecific)#si"
			,"#^(fact|pp\-|permanently protected|temporarily protected)#si"   // wikipedia page protection
			,"#^(infobox|taxobox|navbox|cite|citation|awards|won|nom|end|Persondata)#si"
			,"#^(Please check|Inline citations|Indrefs|Citations|No citations|In\-text citations|Nofootnote|Nocitations|Inline refs needed|Inline\-citations|Inline|Nofootnotes|Needs footnotes|Nofn|No inline citations|Noinline|Inlinerefs|Inline\-sources|In line citation|In\-line citations|Inline|Citations|uw\-biog1|uw\-biog2|uw\-biog3|uw\-biog4)#si"
			,"#^(s\-|Col\-begin|Col\-start|Col\-begin\-small|Col\-break|Col\-2|Col\-1\-of\-2|Col\-2\-of\-2|Col\-3|Col\-1\-of\-3|Col\-2\-of\-3|Col\-3\-of\-3|Col\-4|Col\-1\-of\-4|Col\-2\-of\-4|Col\-3\-of\-4|Col\-4\-of\-4|Col\-5|Col\-1\-of\-5|Col\-2\-of\-5|Col\-3\-of\-5|Col\-4\-of\-5|Col\-5\-of\-5|Col\-end|End|Top|Mid|Bottom|Columns\-start|Column|Columns\-end|Multicol|Multicol\-break|Multicol\-end|Div col|Div col end|col\-float|col\-float\-break|col\-float\-end)#si"
			
			// http://en.wikipedia.org/wiki/Category:Inline_dispute_templates
			,"#^(Chronology citation needed|Contradict\-inline|Copyvio link|Discuss|Irrelevant citation|Neologism inline|POV\-statement|Slang|Spam link|Speculation\-inline|Talkfact|Tone\-inline|Under discussion\-inline|Undue\-inline)#si"
			
			// specific regex rules
			,"#^(cn)$#si"
			,"#^(BLP)#si"
			,"#^([A-Za-z0-9\-\_\s]+?)(BLP)#si"
			
			
			// http://en.wikipedia.org/wiki/Template:Citation_needed/doc#Inline_templates
			,"#^(Attribution needed|Which|Citation needed|Primary source\-inline|Retracted|Third\-party\-inline|Author missing|Author incomplete|Date missing|ISBN missing|Publisher missing|Title incomplete|Year missing|Contradict\-inline|Contradiction\-inline|Examples|Inconsistent|List fact|Lopsided|Clarify timeframe|Update\-small|Where|Year needed|Disambiguation needed|Pronunciation needed|Ambiguous|Awkward|Buzz|Elucidate|Expand acronym|Why|Cite quote|Clarify|Examples|List fact|Nonspecific|Page needed|Citation needed span|Cn\-span|Fact span|Reference necessary|Full|Season needed|Volume needed|Better source|Dead link|Failed verification|Request quotation|Self\-published inline|Source need translation|Verify credibility|Verify source|Definition|Dubious|Technical\-statement|Or|Peacock term|POV\-statement|Quantify|Time fact|Chronology citation needed|Undue\-inline|Vague|Weasel\-inline|When|Who|Whom|By whom|Update after|Cite check|Refimprove|Unreferenced|Citation style|No footnotes)#si"
			
			// other wikis
			,"#^(en|de|fr|nl|it|pl|es|ru|ja|pt|zh|sv|vi|uk|ca|no|fi|cs|hu|fa|ko|ro|id|tr|ar|sk|eo|da|sr|lt|kk|ms|he|eu|bg|sl|vo|hr|war|hi|et|az|gl|nn|simple|la|el|th|new|sh|roa\-rup|oc|mk|ka|tl|ht|pms|te|ta|be\-x\-old|be|br|lv|ceb|sq|jv|mg|cy|mr|lb|is|bs|my|uz|yo|an|lmo|hy|ml|fy|bpy|pnb|sw|bn|io|af|gu|zh\-yue|ne|nds|ur|ku|ast|scn|su|qu|diq|ba|tt|ga|cv|ie|nap|bat\-smg|map\-bms|wa|als|am|kn|gd|bug|tg|zh\-min\-nan|sco|mzn|yi|yec|hif|roa\-tara|ky|arz|os|nah|sah|mn|ckb|sa|pam|hsb|li|mi|si|co|gan|glk|bar|bo|fo|bcl|ilo|mrj|se|nds\-nl|fiu\-vro|tk|vls|ps|gv|rue|dv|nrm|pag|pa|koi|xmf|rm|km|kv|csb|udm|zea|mhr|fur|mt|wuu|lad|lij|ug|pi|sc|or|zh\-classical|bh|nov|ksh|frr|ang|so|kw|stq|nv|hak|ay|frp|ext|szl|pcd|gag|ie|ln|haw|xal|vep|rw|pdc|pfl|eml|gn|krc|crh|ace|to|ce|kl|arc|myv|dsb|as|bjn|pap|tpi|lbe|mdf|wo|jbo|sn|kab|av|cbk\-zam|ty|srn|lez|kbd|lo|ab|tet|mwl|ltg|na|ig|kg|za|kaa|nso|zu|rmy|cu|tn|chy|chr|got|sm|bi|mo|iu|bm|ik|pih|ss|sd|pnt|cdo|ee|ha|ti|bxr|ts|om|ks|ki|ve|sg|rn|cr|lg|dz|ak|ff|tum|fj|st|tw|xh|ny|ch|ng|ii|cho|mh|aa|kj|ho|mus|kr|hz)(?:\s*?)\:#si"
		);
		
		$parser = new Jungle_WikiSyntax_Parser($contents, $wikipedia['title'], $parser_options);
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
	
	function wiki_displayTable($table, $with_headings=false, $echo=false) {
		if(empty($table['data'])) {
			return;
		}
		
		ob_start();
		
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
		
		if($echo) {
			print ob_get_clean();
		} else {
			return ob_get_clean();
		}
	}