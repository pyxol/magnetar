<div class="section_title">IMDb: Connections</div>

<?php
	// Require imdb value
	
	if(!function_exists("isAssoc")) {
		function isAssoc($arr) {
			return array_keys($arr) !== range(0, count($arr) - 1);
		}
	}
	
	if(!empty($entity_meta['external_id']['imdb'])) {
		// Get from database
		$db->select_db(IMDB_DATABASE);
		$imdb_db = $db->get_row("SELECT * FROM `entity` WHERE `seoid` = '". $db->escape($entity_meta['external_id']['imdb']) ."'");
		
		if(!empty($imdb_db['id'])) {
			// get connections
			$imdb_db_connections = $db->get_results("SELECT * FROM `entity_connection` WHERE `entity_from` = '". $db->escape($imdb_db['seoid']) ."' OR `entity_to` = '". $db->escape($imdb_db['seoid']) ."' ORDER BY `connection` ASC");
			$connections = array();
			
			if(!empty($imdb_db_connections)) {
				
				foreach(array_keys($imdb_db_connections) as $idc_key) {
					json_decode($imdb_db_connections[ $idc_key ]['value']);
					
					if(JSON_ERROR_NONE === json_last_error()) {
						$imdb_db_connections[ $idc_key ]['value'] = json_decode($imdb_db_connections[ $idc_key ]['value'], true);
					}
					
					
					$connections[ $imdb_db_connections[ $idc_key ]['connection'] ][] = $imdb_db_connections[ $idc_key ];
				}
				
				
				foreach(array_keys($connections) as $connection_type) {
					?>
					<h2 class="sub_title"><?=esc_html(ucwords(str_ireplace("Cast ", "", str_replace("-", " ", $connection_type))));?></h2>
					<table class="wiki_table">
						<?php /*
						<thead>
							<td>To</td>
							<td>Notes</td>
						</thead>
						*/ ?>
						<tbody>
							<?php foreach(array_keys($connections[ $connection_type ]) as $conn_key): ?>
							<tr>
								<td width="375"><?php
									$link_id = $connections[ $connection_type ][ $conn_key ]['entity_to'];
									
									if($imdb_db['seoid'] === $link_id) {
										$link_id = $connections[ $connection_type ][ $conn_key ]['entity_from'];
									}
									
									$link_imdb_db = $db->get_row("SELECT * FROM `entity` WHERE `seoid` = '". $db->escape($link_id) ."'");
									
									if(!empty($link_imdb_db['id'])) {
										if("episode" === $link_imdb_db['type']) {
											$show_id_row = $db->get_row("SELECT * FROM `entity_connection` WHERE (`entity_from` = '". $db->escape($link_imdb_db['seoid']) ."' OR `entity_to` = '". $db->escape($link_imdb_db['seoid']) ."') AND `connection` = 'tv-show-episode' LIMIT 1");
											
											if(!empty($show_id_row['id'])) {
												$show_id = $show_id_row['entity_from'];
												
												if($link_imdb_db['seoid'] === $show_id) {
													$show_id = $show_id_row['entity_to'];
												}
											}
											
											if(!empty($show_id)) {
												$show_db = $db->get_row("SELECT * FROM `entity` WHERE `seoid` = '". $db->escape($show_id) ."'");
												
												if(!empty($show_db['id'])) {
													print "<div style=\"padding-bottom: 5px;\"><a href=\"#\" title=\"". esc_attr($show_db['seoid']) ."\" target=\"_blank\" style=\"font-weight: bold;\">". esc_html($show_db['title']) ."</a></div>";
												}
											}
											
											
											?>&nbsp;&nbsp;&mdash;&nbsp;&nbsp;&nbsp;<a href="<?=site_url("/dev/spiders/imdb/view.php?query=". $link_imdb_db['seoid']);?>" target="_blank" title="<?=esc_attr($link_imdb_db['seoid']);?>" target="_blank"><?=esc_html($link_imdb_db['title']);?></a><?php
										} else {
											?><a href="<?=site_url("/dev/spiders/imdb/view.php?query=". $link_imdb_db['seoid']);?>" target="_blank" title="<?=esc_attr($link_imdb_db['seoid']);?>" target="_blank" style="font-weight: bold;"><?=esc_html($link_imdb_db['title']);?></a><?php
										}
									} else {
										print esc_html($link_id ." Not Found");
									}
								?></td>
								<td><?php
									if(!empty($connections[ $connection_type ][ $conn_key ]['value'])) {
										$more = $connections[ $connection_type ][ $conn_key ]['value'];
										
										if(!empty($more['characters'])) {
											print "<strong>Character:</strong>\n";
											print "<ul class=\"imdb_list\">";
											
											foreach($more['characters'] as $character) {
												if(!empty($character[':imdb'])) {
													?><li><a href="<?=site_url("/dev/spiders/imdb/view.php?query=". $character[':imdb']);?>" target="_blank" title="<?=esc_attr( $imdb_db['seoid'] ." as ". $character[':imdb'] ." in ". $link_imdb_db['seoid'] );?>"><?=esc_html($character['name']);?></a></li><?php
												} else {
													?><li><?=esc_html($character['name']);?></a></li><?php
												}
											}
											
											print "</ul>\n";
										}
										
										if(!empty($more['rows'])) {
											foreach($more['rows'] as $row) {
												if(!empty($row['role'])) {
													print "<strong>". esc_html($row['role']) ."</strong>";
												}
												
												if(!empty($row['notes'])) {
													print "<ul class=\"imdb_list\">\n";
													
													if(is_array($row['notes'])) {
														foreach($row['notes'] as $note) {
															print "<li>". esc_html($note) ."</li>\n";
														}
													} else {
														print "<li>". esc_html($row['notes']) ."</li>\n";
													}
													
													print "</ul>\n";
												}
											}
										}
									}
								?></td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
					<style type="text/css">
						ul.imdb_list { padding-top: 7px; }
							ul.imdb_list li { list-style: square inside none; margin-left: 10px; margin-bottom: 6px; }
					</style>
					<?php
				}
				
				
				
				
			} else {
				?>
				-- No connections stored! --
				<?php
			}
			
		} else {
			?>
			-- IMDb value not spidered! --
			<?php
		}
		
		$db->select_db(DB_NAME);
	} else {
		?>
		-- No IMDb value set! --
		<?php
	}