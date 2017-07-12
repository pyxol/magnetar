<?php
	$types_id = 0;
	
	$types = array(
		'entertainment_movie' => array(
			'id' => $types_id++,
			'name' => "Entertainment &rsaquo; Movie",
			'table' => "entertainment_movie",
			'title' => "`title`",
			'extra' => "CONCAT(`media_type`, ' - ', `released`)",
			'meta_table' => "entertainment_movie_meta",
			'meta_column' => "entertainment_movie",
			'search_for' => "`title`",
			'url_scheme' => "/entertainment/movie/%SEOID%/",
		),
		'person' => array(
			'id' => $types_id++,
			'name' => "Person",
			'table' => "person",
			'title' => "name",
			'extra' => "CONCAT(DATE(`date_birth`), ' to ', DATE(`date_death`))",
			'meta_table' => "person_meta",
			'meta_column' => "person",
			'search_for' => "`name`",
			'url_scheme' => "/person/%SEOID%/"
		),
	);
	
	$content = (array_key_exists(@$_REQUEST['content_table'], $types)?$types[$_REQUEST['content_table']]:$types['entertainment_movie']);
	
	$results = $db->get_results("
		SELECT
			*, `id`, ". $content['title'] ." as `title`". (!empty($content['extra'])?", ". $content['extra'] ." as `extra`":"") .", ". $content['search_for'] ." as `search_for`
		FROM `". $db->escape($content['table']) ."`
		WHERE NOT EXISTS (
			SELECT `". $db->escape($content['meta_table']) ."`.`". $db->escape($content['meta_column']) ."`
			FROM `". $db->escape($content['meta_table']) ."`
			WHERE
				`". $db->escape($content['meta_table']) ."`.`group` = 'external_id'
				AND `". $db->escape($content['meta_table']) ."`.`key` = 'wikipedia'
				AND `". $db->escape($content['meta_table']) ."`.`". $db->escape($content['meta_column']) ."` = `". $db->escape($content['table']) ."`.`id`
		)
		LIMIT 1
	");
	
	if(empty($results)) {
		print "nothing to do...";
		
		die;
	}
	
	
	$db->select_db("dump_wikipedia");
	
	require_once(DIR_BASE ."dev". DIRECTORY_SEPARATOR ."jungle_wikipedia_parser". DIRECTORY_SEPARATOR ."wiki_parser.php");
	require_once(DIR_BASE ."includes". DIRECTORY_SEPARATOR ."sphinx-2.0.5". DIRECTORY_SEPARATOR ."sphinxapi.php");
	
	foreach($results as $rkey => $rval) {
		// run Sphinx search
		$cl = new SphinxClient_205();
		
		$cl->setServer("localhost", "9312");
		
		$cl->setMatchMode(SPH_MATCH_ALL);
		$cl->setLimits(0, 15);
		
		$cl->setSortMode(SPH_SORT_EXTENDED, "title_length ASC, @weight DESC");
		
		$query_clean = preg_replace(array("#^the #si", "# the$#si"), "", mkurl($rval['search_for'], " "));
		
		$sph_result = $cl->Query("\"". $query_clean ."\"", "index_wikipedia_cleaned");
		
		//print "getLastWarning = ". $cl->getLastWarning() ."<br />\n";
		//print "getLastError = ". $cl->getLastError() ."<br />\n";
		
		if(!empty($sph_result['matches'])) {
			$results[$rkey]['results'] = $db->get_results("SELECT `their_id`, `title` COLLATE utf8_general_ci as `title` FROM `external_wikipedia` WHERE `id` IN ('". implode("','", array_keys($sph_result['matches'])) ."') ORDER BY FIELD(`id`, '". implode("','", array_keys($sph_result['matches'])) ."')");
			//$results[$rkey]['results'] = $db->get_results("SELECT `their_id`, `title` COLLATE utf8_general_ci as `title` FROM `external_wikipedia` WHERE `id` IN ('". implode("','", array_keys($sph_result['matches'])) ."') ORDER BY `their_id`");
		}
		
		unset($cl, $sph_result);
	}
	
	$db->select_db(DB_NAME);
	
	get_header();
?>
	
	<div id="container">
		<ul class="wiki_type_chooser">
		<?php foreach($types as $type_key => $type): ?>
			<li>&middot; <?php if($type['id'] !== $content['id']): ?><a href="?content_table=<?=$type_key;?>"><?=$type['name'];?></a><?php else: ?><b><?=$type['name'];?></b><?php endif; ?></li>
		<?php endforeach; ?>
		</ul>
		<?php if(!empty($results)): ?>
			<ul class="wiki_linker">
				<?php foreach($results as $result): ?>
					<li id="jungle_group_<?=$result['id'];?>">
						<div class="jungle_header">
							<a href="<?=str_ireplace("%SEOID%", $result['seoid'], $content['url_scheme']);?>" target="_blank" title="View the JungleDB Page for '<?=$result['title'];?>"><?=html_entity_decode($result['title'], ENT_COMPAT, "UTF-8");?></a><?=(!empty($result['extra'])?" (". $result['extra'] .")":"");?>&nbsp; &nbsp;[<a href="#" class="wiki_search_inline" data-jungle-table="<?=$content['table'];?>" data-jungle-id="<?=$result['id'];?>">search</a>]&nbsp; &nbsp;[<a href="#" class="wiki_skip" data-jungle-table="<?=$content['table'];?>" data-jungle-id="<?=$result['id'];?>">skip</a>]
						</div>
						
						<div class="inline_search" id="inline_search_<?=$result['id'];?>">
							<input type="text" class="jungle_inline_search" data-jungle-table="<?=$content['table'];?>" data-jungle-id="<?=$result['id'];?>" value="<?=esc_attr($result['title']);?>" />
						</div>
						
						<?php $title_hash = mkurl($result['title']); ?>
						<ul class="possible_wikis" id="possible_wikis_<?=$result['id'];?>">
						<?php if(!empty($result['results'])): ?>
							<?php foreach($result['results'] as $wiki): ?>
							<?php
								$perfect = false;
								
								if(strcmp($title_hash, mkurl($wiki['title'])) === 0) {
									$perfect = true;
								}
								
								
								$id_hash = md5($wiki['their_id']);
								
								$id_group = id2group($wiki['their_id']);
								
								// extract info
								$file_name = "/objstor/pools/jungledb/wiki_pages_raw/". $id_group ."/". substr($id_hash, 0, 2) ."/". substr($id_hash, 2, 2) ."/". substr($id_hash, 4);
								$finfo = new finfo(FILEINFO_MIME, "/usr/share/misc/magic");
								$file_mime = $finfo->file($file_name);
								
								//header("Content-Type: text/plain");
								
								$contents = implode(file($file_name));
								convert_to_utf8($contents);
								
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
								
								$parser = new Jungle_WikiSyntax_Parser($contents, $wiki['title'], $parser_options);
								//$parsed = $parser->parse();
								$parsed = $parser->parse(array('citations', 'wikipedia_meta'));
							?>
							<li>
								[<a href="#" class="select_wiki" data-jungle-table="<?=$content['table'];?>" data-jungle-id="<?=$result['id'];?>" data-wiki-id="<?=$wiki['their_id'];?>">select</a>] [<a href="/dev/wiki.php?e=<?=$wiki['their_id'];?>" target="_blank">view</a>] <b><?=(!empty($perfect)?"<u>":"").html_entity_decode($wiki['title'], ENT_COMPAT, "UTF-8").(!empty($perfect)?"</u>":"");?></b><?=($parsed['page_attributes']['content_type'] !== ":unknown"?" -- ". $parsed['page_attributes']['content_type']:"");?><br />
								<?php
									$section = array_shift($parsed['sections']);
									$intro = "";
									if(!empty($section['text'])) {
										$intro = $section['text'];
									}
									
									$intro = preg_replace("#\[wiki=([a-f0-9]{32})(?:\#(?:[^\]]+?))?\]([^\]]+?)\[/wiki\]#si", "<a href=\"\\1\">\\2</a>", $intro);
									print nl2br($intro);
								?>
							</li>
							<?php endforeach; ?>
						<?php else: ?>
							No results found...
						<?php endif; ?>
						</ul>
				<?php endforeach; ?>
			</ul>
		<?php else: ?>
			Nothing to do.
		<?php endif; ?>
	</div>
	
	<style type="text/css">
		.jungle_header { font-size: 20px; padding-bottom: 5px; }
			.jungle_header a { font-size: 20px; }
		.inline_search { display: none; }
		
		.wiki_type_chooser { display: table; width: 100%; margin: 0 0 15px 0; padding: 0 0 10px 0; border-bottom: 1px solid #303030; }
			.wiki_type_chooser li { display: inline-block; margin: 0 15px 0 0; padding: 0; font: normal normal normal 11px/11px Verdana; color: #303030; }
		
		.wiki_linker { display: block; margin: 0 0 20px 0; padding: 0; }
			.wiki_linker > li { display: block; margin: 0 0 20px 0; border-bottom: 1px solid #c0c0c0; padding: 0 5px 20px 5px; }
				.wiki_linker > li a { text-decoration: none; }
					.wiki_linker > li a:hover { text-decoration: underline; }
		
		.possible_wikis li { padding: 3px 7px; list-style: square; margin-left: 20px; margin-bottom: 15px; }
			.possible_wikis li:hover { background-color: #f0f0f0; }
	</style>
	
	<script type="text/javascript">
	<?php ob_start(); ?>
		;jQuery(document).ready(function($) {
			$("body").on('click', ".load_more", function(e) {
				e.preventDefault();
				
				$.ajax({
					'url': "/ajax/wiki_link.php",
					'type': "GET",
					'cache': false,
					'data': {'action': "load_more"},
					'success': function(data) {
						if(data.status === "error") {
							alert("Error: "+ data.message);
							
							return;
						}
						
						$(".wiki_linker").append(data.cargo);
					}
				});
			});
			
			$("body").on('click', ".wiki_search_inline", function(e) {
				e.preventDefault();
				
				var jungle_table = $(this).attr('data-jungle-table') || false;
				var jungle_id = $(this).attr('data-jungle-id') || false;
				
				if(!jungle_table || !jungle_id) {
					console.log("Link is malformed.");
					
					return;
				}
				
				if(!$("#inline_search_"+ jungle_id).is(":visible")) {
					$("#inline_search_"+ jungle_id).slideDown();
				}
			});
			
			$(".jungle_inline_search").on('keyup', function(e) {
				e.preventDefault();
				
				if(e.which !== 13) {
					return;
				}
				
				var jungle_table = $(this).attr('data-jungle-table') || false;
				var jungle_id = $(this).attr('data-jungle-id') || false;
				
				if(!jungle_table || !jungle_id) {
					console.log("Input box is malformed.");
					
					return;
				}
				
				var search_query = $(this).val().trim() || false;
				
				if(!search_query) {
					return;
				}
				
				if(search_query.length < 2) {
					return;
				}
				
				$.ajax({
					'url': "/ajax/wiki_link.php",
					'type': "GET",
					'data': {'action': "inline_search", 'query': search_query, 'jungle_table': jungle_table, 'jungle_id': jungle_id},
					'success': function(data) {
						console.dir(data);
						
						$("#possible_wikis_"+ data.cargo.jungle_id).empty();
						for(var i in data.cargo.results) {
							$("<li>[<a href='#' class='select_wiki' data-jungle-table='"+ data.cargo.jungle_table +"' data-jungle-id='"+ data.cargo.jungle_id +"' data-wiki-id='"+ data.cargo.results[i].their_id +"'>select</a>] [<a href='/dev/wiki.php?e="+ data.cargo.results[i].their_id +"' target='_blank'>view</a>] "+ data.cargo.results[i].title +"</li>").appendTo("#possible_wikis_"+ data.cargo.jungle_id);
						}
					}
				})
			});
			
			$("body").on('click', ".wiki_skip", function(e) {
				e.preventDefault();
				
				var jungle_table = $(this).attr('data-jungle-table') || false;
				var jungle_id = $(this).attr('data-jungle-id') || false;
				
				if(!jungle_table || !jungle_id) {
					console.log("Link is malformed.");
					
					return;
				}
				
				//alert("jungle_table="+ jungle_table +"&jungle_id="+ jungle_id);
				
				$.ajax({
					'url': "/ajax/wiki_link.php",
					'type': "POST",
					'cache': false,
					'context': $("#jungle_group_"+ jungle_id),
					'data': {'jungle_table': jungle_table, 'jungle_id': jungle_id, 'skip': "true"},
					'success': function(data) {
						if(data.status === "error") {
							alert("Error: "+ data.status_msg);
							
							return;
						}
						
						$(this).slideUp(1000, function() { $(this).remove(); });
					}
				});
			});
			
			$("body").on('click', ".select_wiki", function(e) {
				e.preventDefault();
				
				var jungle_table = $(this).attr('data-jungle-table') || false;
				var jungle_id = $(this).attr('data-jungle-id') || false;
				var wiki_id = $(this).attr('data-wiki-id') || false;
				
				if(!jungle_table || !jungle_id || !wiki_id) {
					console.log("Link is malformed.");
					
					return;
				}
				
				//alert("jungle_table="+ jungle_table +"&jungle_id="+ jungle_id +"&wiki_id="+ wiki_id);
				
				var action = "";
				
				if($(this).hasClass("is_wiki_child")) {
					action = "is_child";
				}
				
				$.ajax({
					'url': "/ajax/wiki_link.php",
					'type': "POST",
					'cache': false,
					'context': $("#jungle_group_"+ jungle_id),
					'data': {'action': action, 'jungle_table': jungle_table, 'jungle_id': jungle_id, 'wiki_id': wiki_id},
					'success': function(data) {
						if(data.status === "error") {
							alert("Error: "+ data.status_msg);
							
							return;
						}
						
						$(this).slideUp(1000, function() { $(this).remove(); });
					}
				});
			});
		});
	<?php queue_js_inline( ob_get_contents() ); ob_end_clean(); ?>
	</script>
	
<?php
	get_footer();