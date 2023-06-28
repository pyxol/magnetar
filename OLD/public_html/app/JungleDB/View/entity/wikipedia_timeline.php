<h2 class="section_title">Wikipedia Timeline</h2>

<?php
	$parsed = false;
	
	if(!empty($entity_meta['external_id']['wikipedia'])) {
		$parsed = pullAndParse_wikipediaById($entity_meta['external_id']['wikipedia'], array('citations', 'wikipedia_meta'));
	}
	
	if(!empty($parsed)) {
		$wikipedia_id = $entity_meta['external_id']['wikipedia'];
		
		global $section_texts;
		$section_texts = "";
		
		function extract_wiki_section_text($section, $depth=1) {
			global $section_texts;
			
			if(!empty($section['text'])) {
				$section_texts .= $section['text'] . PHP_EOL;
			}
			
			if(!empty($section['children'])) {
				foreach($section['children'] as $child) {
					extract_wiki_section_text($child, ($depth + 1));
				}
			}
		}
		
		if(!empty($parsed['sections'])) {
			foreach($parsed['sections'] as $section) {
				extract_wiki_section_text($section, 1);
			}
		}
		
		if(!empty($section_texts)) {
			$sentences = text_to_sentences($section_texts, array("[wiki=" => "["));
			
			// Generally the first sentence isn't really necessary, and it's more of a generalization for the wiki entry.
			// So, with that, let's get it off the array
			$first_line = (!empty($sentences)?array_shift($sentences):false);
			
			
			preg_match_all("#\b(\d{4})\b#si", $section_texts, $dates);
			
			$year_low = min($dates[1]);
			$year_high = max($dates[1]);
			
			if(isset($parsed['meta_boxes']['persondata']['date_of_birth'])) {
				$date_birth = strtotime($parsed['meta_boxes']['persondata']['date_of_birth']);
				
				if(preg_match("#^\d{4}$#si", date("Y", $date_birth))) {
					$year_low = date("Y", $date_birth);
				}
			}
			
			if(isset($parsed['meta_boxes']['persondata']['date_of_death'])) {
				$date_death = strtotime($parsed['meta_boxes']['persondata']['date_of_death']);
				
				if(preg_match("#^\d{4}$#si", date("Y", $date_death))) {
					$year_high = date("Y", $date_death);
				}
			}
			
			$year_low = intval($year_low);
			$year_high = intval($year_high);
			
			$year_high = min($year_high, date("Y"));
			
			
			// Start the timeline
			$timeline = array();
			
			print "<table width=\"100%\">";
			
			$already_used = array();
			
			foreach($sentences as $skey => $sentence) {
				$sentence = preg_replace("#\[wiki=([a-f0-9]{32})(\#[^\]]+)?\]([^\]]+?)\[\/wiki\]#si", "\\3", $sentence);
				
				if(!preg_match_all("#(?:[^A-Za-z0-9])?(\d{4})(?:[^A-Za-z0-9])?#si", $sentence, $match)) {
					continue;
				}
				
				foreach($match[1] as $year_match) {
					if(in_array($skey, $already_used)) {
						continue;
					}
					
					if(($year_match < $year_low) || ($year_match > $year_high)) {
						if(!isset($timeline['outlier'])) {
							$timeline['outlier'] = array();
						}
						
						$timeline['outlier'][] = array(
							'year' => $year_match,
							'sentence' => $sentences[ $skey ],
						);
						
						$already_used[] = $skey;
						
						continue;
					}
					
					if(!isset($timeline[$year_match])) {
						$timeline[$year_match] = array();
					}
					
					$timeline[$year_match][] = array('sentence' => $sentences[ $skey ]);
					$already_used[] = $skey;
				}
			}
			
			// add in meta data
			if(!empty($date_birth)) {
				if(!isset($timeline[ intval(date("Y", $date_birth)) ])) {
					$timeline[ intval(date("Y", $date_birth)) ] = array();
				}
				
				if(isset($parsed['meta_boxes']['persondata']['place_of_birth'])) {
					if(is_array($parsed['meta_boxes']['persondata']['place_of_birth'])) {
						$parsed['meta_boxes']['persondata']['place_of_birth'] = implode(", ", $parsed['meta_boxes']['persondata']['place_of_birth']);
					}
				}
				
				$has_born_already = false;
				
				if(!empty($timeline[ intval(date("Y", $date_birth)) ])) {
					foreach($timeline[ intval(date("Y", $date_birth)) ] as $timeline_born_line) {
						if(preg_match("#\s*born\s*#si", $timeline_born_line['sentence'])) {
							$has_born_already = true;
							
							break;
						}
					}
				}
				
				if($has_born_already === false) {
					$timeline[ intval(date("Y", $date_birth)) ][] = array(
						'date' => $date_birth,
						'sentence' => "Born". (isset($parsed['meta_boxes']['persondata']['place_of_birth'])?" in ". trim( $parsed['meta_boxes']['persondata']['place_of_birth'] ):""),
					);
				}
			}
			
			if(!empty($date_death)) {
				if(!isset($timeline[ intval(date("Y", $date_death)) ])) {
					$timeline[ intval(date("Y", $date_death)) ] = array();
				}
				
				if(isset($parsed['meta_boxes']['persondata']['place_of_death'])) {
					if(is_array($parsed['meta_boxes']['persondata']['place_of_death'])) {
						$parsed['meta_boxes']['persondata']['place_of_death'] = implode(", ", $parsed['meta_boxes']['persondata']['place_of_death']);
					}
				}
				
				$has_died_already = false;
				
				if(!empty($timeline[ intval(date("Y", $date_death)) ])) {
					foreach($timeline[ intval(date("Y", $date_death)) ] as $timeline_died_line) {
						if(preg_match("#\s*(died|passed away|death)\s*#si", $timeline_died_line['sentence'])) {
							$has_died_already = true;
							
							break;
						}
					}
				}
				
				if($has_died_already === false) {
					$timeline[ intval(date("Y", $date_death)) ][] = array(
						'date' => $date_death,
						'sentence' => "Died". (isset($parsed['meta_boxes']['persondata']['place_of_death'])?" in ". trim($parsed['meta_boxes']['persondata']['place_of_death']):""),
					);
				}
			}
			
			
			
			$started_yet = false;
			
			for($year = $year_low; $year <= $year_high; $year++) {
				if($started_yet === false && empty($timeline[$year])) {
					continue;
				}
				
				$started_yet = true;
				
				if(empty($timeline[$year])) {
					$starting_year = $year;
					
					while(empty($timeline[($year + 1)]) && $year <= $year_high) {
						$year++;
					}
					
					?>
					<tr><td colspan="2">&nbsp;</td></tr>
					<tr>
						<td colspan="2">
							<div style="background-color: #e0e0e0; height: 1px; text-align: center; margin: 0 30px;">
								<span style="background-color: #fafafa; text-align: center; padding: 0 4px; position: relative; top: -0.5em; left: 10px; font: italic normal normal 14px/14px Verdana; color: #999;">
									<?=$starting_year . ($starting_year !== $year?"&#151;". (substr($starting_year, 0, 2) === substr($year, 0, 2)?substr($year, 2):$year):"");?>
								</span>
							</div>
						</td>
					</tr>
					<tr><td colspan="2">&nbsp;</td></tr>
					<?php
					continue;
				}
				
				print "<tr style=\"background-color: #fff;\">\n";
				print "	<td width=\"70\">\n";
				print "		<div style=\"text-align: center; padding: 5px; font-size: 16px;". (empty($timeline[$year])?" color: #c0c0c0;":"") ."\">". $year ."</div>\n";
				
				if(!empty($date_birth)) {
					$birth_year = date("Y", $date_birth);
					$age = ($year - $birth_year);
					
					if($age > 0) {
						if(empty($timeline[ ($year - 1) ]) || ($age % 10) === 0) {
							print "<div style=\"text-align: center\">Age<br /><strong>". ($year - $birth_year) ."</strong></div>\n";
						}
					}
				}
				
				print "	</td>\n";
				print "	<td>\n";
				
				if(!empty($timeline[$year])) {
					print "<ul class=\"wiki_timeline_year\">\n";
					
					$sorted_keys = array();
					
					foreach($timeline[$year] as $ekey => $entry) {
						$exact_date = false;
						
						if($exact_date === false && !empty($entry['date'])) {
							$exact_date = $entry['date'];
						}
						
						if($exact_date === false) {
							if(preg_match("#(January|February|March|April|May|June|July|August|September|October|November|December|Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sept?|Oct|Nov|Dec)\s+(\d{1,2}),?#si", $entry['sentence'], $em)) {
								$exact_date = strtotime($em[1] ." ". $em[2] .", ". $year);
							}
						}
						
						if($exact_date === false) {
							if(preg_match("#(January|February|March|April|May|June|July|August|September|October|November|December|Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sept?|Oct|Nov|Dec),?#si", $entry['sentence'], $em)) {
								$exact_date = strtotime($em[1] ." ". $year);
							}
						}
						
						if(!empty($exact_date)) {
							if(!isset($timeline[$year][$ekey]['date'])) {
								$timeline[$year][$ekey]['date'] = $exact_date;
							}
							
							if(!isset($sorted_keys[ date("z", $exact_date) ])) {
								$sorted_keys[ date("z", $exact_date) ] = array();
							}
							
							$sorted_keys[ date("z", $exact_date) ][] = $ekey;
						} else {
							if(!isset($sorted_keys['nil'])) {
								$sorted_keys['nil'] = array();
							}
							
							$sorted_keys['nil'][] = $ekey;
						}
					}
					
					ksort($sorted_keys);
					
					if(isset($sorted_keys['nil'])) {
						$nil = $sorted_keys['nil'];
						unset($sorted_keys['nil']);
						
						if(!empty($nil)) {
							// where to place in array:
							//array_unshift($sorted_keys, $nil);
							array_push($sorted_keys, $nil);
							unset($nil);
						}
					}
					
					foreach(array_keys($sorted_keys) as $sk_date) {
						foreach($sorted_keys[$sk_date] as $ekey) {
							$entry = $timeline[$year][$ekey];
							
							print "<li>";
							
							if(!empty($entry['date'])) {
								print "<b>". date("M d", $entry['date']) ."</b> - ";
							}
							
							
							// padding to help regex
							$entry['sentence'] = "     ". $entry['sentence'] ."     ";
							
							global $shortcode_list;
							$shortcode_list = array();
							
							$entry['sentence'] = preg_replace_callback("/\[([A-Za-z0-9\-\_]+)=([^\]]+)\]([^\[]*)\[\/\\1\]/s", function($match) {
								global $shortcode_list;
								
								$shortcode_signed = "#JDB#SHCD". substr(md5($match[0]), 0, 3) ."#JDB#";
								
								$shortcode_list[ $shortcode_signed ] = $match[0];
								
								return $shortcode_signed;
							}, $entry['sentence']);
							
							$entry['sentence'] = preg_replace("/\s((([ever|and]\s+)?since|between|before|after|in|during|on|as of|)\s+)?((January|February|March|April|May|June|July|August|September|October|November|December|Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sept?|Oct|Nov|Dec)(,?\s+\d{1,2},?)?\s+)?(\d{4}|\'\d{2})(\s*(\-|to|\'?till?|\,?\s*and)\s*(\d{4}|\'\d{2}))?(\,|\:|\;|\'|\"|\.)?\s/i", " ", $entry['sentence']);
							
							
							if(!empty($shortcode_list)) {
								$entry['sentence'] = str_replace(array_keys($shortcode_list), array_values($shortcode_list), $entry['sentence']);
							}
							
							// remove inline date notes (dkjdsfkjsf (2005))
							$entry['sentence'] = preg_replace("#\s+(\(|\])(?:pub|ca|c|circa)?(\.|". preg_quote("&#046;", "#") .")?\s*([0-9]{4})\s*(\)|\])#si", "", $entry['sentence']);
							
							$entry['sentence'] = trim($entry['sentence'], " .;:*#");
							
							// Don't add a trailing period on sentences that are just "[wiki=...]...[/wiki]" or "[wiki=...]...[/wiki] (...)""
							if(!preg_match("#^\s*\[([A-Za-z0-9\-\_]+)=([^\]]*)\]([^\[]*)\[\/\\1\](\s*\(([^\)]+)\))?\s*$#si", $entry['sentence'])) {
								$entry['sentence'] .= ".";
							}
							
							print jungleWiki(ucfirst($entry['sentence']), false);
							//print $entry['sentence'];
							print "</li>\n";
						}
					}
					
					print "</ul>";
				} else {
					print "&nbsp;";
				}
				print "	</td>\n";
				print "</tr>\n";
				print "<tr><td colspan=\"2\">&nbsp;</td></tr>\n";
			}
			
			print "</table>\n";
		} else {
			print "<p><i>No data #1</i></p>\n";
		}
		
		print "<p>debug: <a href=\"/dev/wiki.php?e=". $wikipedia_id ."\" target=\"_blank\" style=\"font-weight: bold;\">view full parse</a></p>\n";
	} else {
		print "<p>This entity is not attached to a specific wikipedia pageid.</p>";
	}