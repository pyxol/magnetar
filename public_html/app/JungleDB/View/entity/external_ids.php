<?php api::tpl()->view('header'); ?>

<?php
	/*
	if(!empty($_POST['form_submitted'])) {
		if(!empty($_POST['external_id_update'])) {
			foreach($_POST['external_id_update'] as $eid_key => $eid_value) {
				$eid_key = trim(strtolower($eid_key));
				$eid_value = trim($eid_value);
				
				if(strlen($eid_key) > 0 && strlen($eid_value) > 0) {
					$db->update("entity_meta", array(
						'value'		=> $eid_value,
					),
					array(
						'entity_id'	=> $entity['id'],
						'meta_id'	=> $meta['external_id:'. $eid_key]['id'],
					));
				}
			}
			
			redirect("/j/". $entity['id'] ."/external-ids.html#=success=");
			
			die;
		}
	}
	
	function wikipedia_externalIdParse($eid, $eval) {
		return $eval;
		
		switch($eid) {
			case 'imdb':
				$id = str_pad($eval['id'], 7, '0', STR_PAD_LEFT);
				
				switch(strtolower(trim($eval['type']))) {
					case 'name':
					case 'person':
						return("nm". $id);
					break;
					
					case 'title':
					case 'episode':
						return("tt". $id);
					break;
					
					case 'company':
						return("co". $id);
					break;
					
					case 'character':
						return("ch". $id);
					break;
				}
			break;
			
			case 'rotten_tomatoes':
				return $eval['id'];
			break;
			
			case 'tvtropes':
			case 'ning':
			case 'nndb':
				return array_shift($eval);
			break;
			
			case 'myspace':
			case 'facebook':
			case 'twitter':
				return (!empty($eval['id'])?$eval['id']:(!empty($eval['username'])?$eval['username']:json_encode($eval)));
			break;
			
			default:
				return (is_array($eval)?json_encode($eval):$eval);
		}
	}
	*/
?>
	
	<?php api::tpl()->view('entity/wrapper_header'); ?>
		
		<h2 class="section_title">External IDs</h2>
		
		<h3 class="sub_title">Active Records</h3>
		
		<?php if(!empty(api::tpl()->entity_meta['external_id'])): ?>
			<form method="post" action="/j/<?=$entity['id'];?>/external-ids.html">
			<input type="hidden" name="form_submitted" value="true" />
			<table border="1" class="wiki_table">
				<thead>
					<tr>
						<td>key</td>
						<td>value</td>
					</tr>
				</thead>
				
				<?php foreach(api::tpl()->entity_meta['external_id'] as $eid_key => $eid_value): ?>
				<tbody>
					<tr>
						<td width="20%"><?=$eid_key;?></td>
						<td width="80%" class="double_click_edit">
							<input type="text" name="external_id_update[<?=esc_attr($eid_key);?>]" value="<?=esc_attr($eid_value);?>" size="70" />
						</td>
					</tr>
				</tbody>
				<?php endforeach; ?>
				
				<tbody>
					<tr>
						<td width="20%">&nbsp;</td>
						<td width="80%"><input type="submit" value="Save Changes" /></td>
					</tr>
				</tbody>
			</table>
			</form>
		<?php else: ?>
			<p>There are no actively recorded external ids at the moment.</p>
		<?php endif; ?>
		
		<?php if(!empty(api::tpl()->entity_meta['external_id']['wikipedia'])): ?>
		<h3 class="sub_title">Parsed from Wikipedia</h3>
		<p>debug: <a href="/dev/wiki.php?e=<?=api::tpl()->entity_meta['external_id']['wikipedia'];?>" target="_blank" style="font-weight: bold;">view full parse</a></p>
		
		<?php
			$parsed = pullAndParse_wikipediaById(api::tpl()->entity_meta['external_id']['wikipedia'], array('citations', 'wikipedia_meta'));
			
			$show_links = false;
			
			if(!empty($parsed['external_links'])) {
				foreach($parsed['external_links'] as $eid_key => $eid_value) {
					if(in_array($eid_key, array("amg", "allmovie", "allmusic", "allrovi"))) {
						$eid_key = "allmovie";
					}
					
					if(substr($eid_key, 0, 1) === ":") {
						continue;
					}
					
					if(isset(api::tpl()->entity_meta['external_id'][ $eid_key ])) {
						continue;
					}
					
					$show_links = true;
					
					break;
				}
			}
			
			if(!empty($show_links)) {
				?>
			<table border="1" class="wiki_table">
				<thead>
					<tr>
						<td>key</td>
						<td>parsed / value</td>
					</tr>
				</thead>
				<?php
					//print "<pre>". print_r(api::tpl()->entity_meta,1) ."</pre>\n";
					//print "<pre>". print_r($parsed['external_links'],1) ."</pre>\n";
					
					foreach($parsed['external_links'] as $eid_key => $eid_value):
						if(in_array($eid_key, array("amg", "allmovie", "allmusic", "allrovi"))) {
							$eid_key = "allmovie";
						}
						
						if(substr($eid_key, 0, 1) === ":") {
							continue;
						}
						
						if(isset(api::tpl()->entity_meta['external_id'][ $eid_key ])) {
							continue;
						}
				?>
				<tbody id="wiki_parse_external_id_<?=md5($eid_key);?>">
				<tr class="wiki_table_tbody_first_row">
					<td width="20%" rowspan="3"><b><?=$eid_key;?></b></td>
					<td width="80%">
						<?=wikipedia_externalIdParse($eid_key, $eid_value);?>
					</td>
				</tr>
				<tr>
					<td width="80%">
						<?php if(is_array($eid_value)): ?><pre><?=print_r($eid_value,1);?></pre><?php else: ?><?=$eid_value;?><?php endif; ?>
					</td>
				</tr>
				<tr class="wiki_table_tbody_last_row">
					<td width="80%">
						<?php if($eid_key !== "url" && array_key_exists($eid_key, api::tpl()->entity_meta['external_id'])): ?>
						<b>Already Exists</b>
						<?php else: ?>
						[<a href="#" class="add_entity_meta" data-entity_id="<?=esc_attr($entity['id']);?>" data-key="external_id:<?=$eid_key.($eid_key == "url"?":":"");?>" data-value="<?=htmlentities(wikipedia_externalIdParse($eid_key, $eid_value));?>">save record</a>]
						<?php endif; ?>
					</td>
				</tr>
				</tbody>
				<tr><td colspan="2">&nbsp;</td></tr>
				<?php endforeach; ?>
			</table>
				<?php
			} else {
				print "<p><i>Could not find any additional external links.</i></p>\n";
			}
		?>
		<?php else: ?>
			<h2 class="section_title">Search Wikipedia</h2>
			
			<form id="wiki_search_form">
				Search: <input type="text" id="wiki_search_query" value="<?=esc_attr($entity['title']);?>" /> <input type="submit" value="Search" />
			</form>
			
			<ul id="wiki_search_results" class="entity__external_id_list"></ul>
		<?php endif; ?>
		
		<?php if(empty(api::tpl()->entity_meta['external_id']['imdb'])): ?>
			<h2 class="section_title">Search IMDb</h2>
			
			<form id="imdb_search_form">
				Search: <input type="text" id="imdb_search_query" value="<?=esc_attr($entity['title']);?>" /> <input type="submit" value="Search" />
			</form>
			
			<ul id="imdb_search_results" class="entity__external_id_list"></ul>
		<?php endif; ?>
		
	<?php api::tpl()->view('entity/wrapper_footer'); ?>
	
	<script type="text/javascript">
		;jQuery(document).ready(function($) {
			<?php if(empty(api::tpl()->entity_meta['external_id']['wikipedia'])): ?>
			$("#wiki_search_form").submit(function(e) {
				e.preventDefault();
				
				var wiki_search_query = $("#wiki_search_query").val();
				
				if(wiki_search_query.length < 1) {
					return;
				}
				
				$.ajax({
					'url':			"/ajax/wiki_search.php",
					'data':			{
						'query':	wiki_search_query
					},
					
					'beforeSend':	function() {
						$("#wiki_search_results").empty();
					},
					
					'success':		function(data) {
						if(data.status === undefined) {
							return;
						}
						
						if(data.status === "error") {
							alert("Ajax Error: "+ data.status);
							
							return;
						}
						
						if(data.cargo.total_found == "0") {
							$("#wiki_search_results").append( $("<li/>").css({'text-align': "center", 'padding': "10px"}).html("No results found for '"+ data.cargo.query +"'") );
						}
						
						var key;
						
						for(key in data.cargo.results) {
							if(data.cargo.results.hasOwnProperty(key)) {
								$("<li/>", {
									'id':	"jungle_wiki_"+ data.cargo.results[key].wiki_id
								}).html("[<a href=\"#\" class=\"add_entity_meta\" data-entity_id=\"<?=$entity['id'];?>\" data-key=\"external_id:wikipedia\" data-value=\""+ data.cargo.results[key].wiki_id +"\">select primary</a>] "+
										"[<a href=\"/dev/wiki.php?e="+ data.cargo.results[key].wiki_id +"\" target=\"_blank\">view</a>] "+
										"<b>"+ data.cargo.results[key].title +"</b>"+
										"").appendTo("#wiki_search_results");
							}
						}
					}
				});
			});
			
			if($("#wiki_search_form")) {
				$("#wiki_search_form").submit();
			}
			<?php endif; ?>
			
			<?php if(empty(api::tpl()->entity_meta['external_id']['imdb'])): ?>
			$("#imdb_search_form").submit(function(e) {
				e.preventDefault();
				
				var imdb_search_query = $("#imdb_search_query").val();
				
				if(imdb_search_query.length < 1) {
					return;
				}
				
				$.ajax({
					'url':			"/ajax/imdb_search.php",
					'data':			{
						'query':	imdb_search_query
					},
					
					'beforeSend':	function() {
						$("#imdb_search_results").empty();
					},
					
					'success':		function(data) {
						if(data.status === undefined) {
							return;
						}
						
						if(data.status === "error") {
							alert("Ajax Error: "+ data.status);
							
							return;
						}
						
						if(!data.cargo.total_found || (parseInt(data.cargo.total_found, 10) <= 0)) {
							$("#imdb_search_results").append( $("<li/>").css({'text-align': "center", 'padding': "10px"}).html("No results found for '"+ data.cargo.query +"'") );
						}
						
						var key;
						
						for(key in data.cargo.results) {
							if(data.cargo.results.hasOwnProperty(key)) {
								$("<li/>", {
									'id':	"jungle_imdb_"+ data.cargo.results[key].imdb_key
								}).html("[<a href=\"#\" class=\"add_entity_meta\" data-entity_id=\"<?=$entity['id'];?>\" data-key=\"external_id:imdb\" data-value=\""+ data.cargo.results[key].imdb_key +"\">select primary</a>] "+
										"[<a href=\"/dev/imdb.php?d="+ data.cargo.results[key].imdb_key +"\" target=\"_blank\">view</a>] "+
										"[<a href=\"http://www.imdb.com/find?s=all&q="+ data.cargo.results[key].imdb_key +"\" target=\"_blank\">imdb.com</a>] "+
										"<b>"+ data.cargo.results[key].title +"</b>&nbsp; &nbsp;[<i>"+ data.cargo.results[key].type +"</i>]"+
										"").appendTo("#imdb_search_results");
							}
						}
					}
				});
			});
			
			if($("#imdb_search_form")) {
				$("#imdb_search_form").submit();
			}
			<?php endif; ?>
		});
	</script>
	
<?php api::tpl()->view('footer'); ?>