<?php api::tpl()->view('header'); ?>
	
	<div id="app" class="two-sided app__search">
	
		<div id="facade">
			<ul class="search_refine">
			<?php
				/*
				$ent_list = array();
				$ent_list_child = array();
				
				if(!empty($search['entity_type'])) {
					$parent_id = $entity_type['id'];
					
					$ent_list[] = $entity_type['id'];
					
					if(!empty($entity_types_by_parent_id[ $search['entity_type'] ])) {
						foreach($entity_types_by_parent_id[ $search['entity_type'] ] as $ent_type) {
							$ent_list[] = $ent_type['id'];
							
							$ent_list_child[] = $ent_type['id'];
						}
					}
				} else {
					// just list base entity types
					foreach($entity_types_by_parent_id['0'] as $ent_type) {
						if($ent_type['display_always'] == "1") {
							$ent_list[] = $ent_type['id'];
						}
					}
				}
				
				if(!empty($ent_list)) {
					foreach($ent_list as $ent_type_id) {
						$ent_type = $entity_types[ $ent_type_id ];
						$ent_type_num_matches = 0;
						$ent_type_family_ids = getEntityTypeIdAndChildIds($ent_type['id']);
						
						if(!empty($ent_type_family_ids)) {
							foreach($ent_type_family_ids as $etfid) {
								if(!empty($results_by_entity_type[ $etfid ])) {
									$ent_type_num_matches += $results_by_entity_type[ $etfid ];
								}
							}
						}
						
						if(empty($ent_type_num_matches)) {
							continue;
						}
						?>
						<li<?=(in_array($ent_type['id'], $ent_list_child)?" class=\"search_type_refine_child\"":"");?>><a href="<?=search_url($search['query'], 1, $ent_type['id']);?>" class="search_type_refine_anchor<?=((!empty($entity_type) && ($ent_type['id'] == $entity_type['id']))?" search_type_refine_anchor_selected":"");?> ajax" entity-type="<?=esc_attr($ent_type['id']);?>">
							<span class="search_type_title"><?=esc_html($ent_type['title']);?></span>
							<span class="search_type_count"><?=number_format($ent_type_num_matches);?></span>
						</a></li>
						<?php
					}
				}
			?>
			</ul>
			
			<?php if(!empty($search['entity_type'])): ?><a href="<?=search_url($search['query']);?>" title="<?=esc_attr("Search for '". $search['query'] ."'");?>" class="ajax">Reset Search</a><?php endif; */ ?>
		</div>
		
		<div id="main">
			<h1 class="page_title">Results for <span>"<?=api::tpl()->query;?>"</span></h1>
			<div class="page_breadcrumb"><?php if(api::tpl()->num_results > 0): ?>showing <span><?=number_format(api::tpl()->num_result_start);?>-<?=number_format(api::tpl()->num_result_end);?></span> of <span><?php endif; ?><?=number_format(api::tpl()->num_results);?> found</span></div>
			
			<div id="main_wrapper">
				<?php if(!empty($dym)): ?>
				<div id="dym">
					<?=$dym;?>
				</div>
				<?php endif; ?>
					
				<?php if(!empty(api::tpl()->num_results)): ?>
					<!-- Search Results -->
					<div id="search_results">
						<?php
							foreach(api::tpl()->results as $result):
								/*
								$entity_type_parent_ids = array();
								$next_entity_type_id = $result['type_id'];
								$entity_type_ids = array();   // list of used entity type ids to stop endless looping
								
								if(!empty($next_entity_type_id)) {
									do {
										if(in_array($next_entity_type_id, $entity_type_parent_ids)) {
											break;
										}
										
										$entity_type_parent_ids[] = $next_entity_type_id;
										$next_entity_type_id = $entity_types[$next_entity_type_id]['parent_id'];
									} while(!empty($next_entity_type_id));
								}
								
								unset($next_entity_type_id);
								$entity_type_parent_ids = array_reverse($entity_type_parent_ids);
								*/
							?>
							<div class="search_result<?=(!$result->getPrimaryMediaId()?" search_result_thumbless":"");?>"><a href="<?=$result->getUrl();?>" title="<?=$result->getTitle();?>" class="search_result_anchor ajax">
								<div class="search_result_thumb_container">
									<img src="<?=($result->getPrimaryMediaId()?$result->getPrimaryMedia()->getThumb('n'):"/static/images/no_thumb.png");?>" alt="<?=$result->getTitle();?>" class="search_result_thumb" />
								</div>
								<div class="search_result_text_container">
									<div class="search_result_bar">
										<h3 class="search_result_title"><?=$result->getTitle();?></h3>
										<div class="search_result_breadcrumb">
											<strong><?=$result->getType()->getTitle();?></strong>
											<?php
												/*
												$i = 0;
												
												foreach($entity_type_parent_ids as $enttype_id) {
													if(++$i > 1) { print " &rsaquo; "; }
													if($enttype_id == $result['type_id']) { print "<strong>"; }
													
													print esc_html($entity_types[ $enttype_id ]['title']);
													
													if($enttype_id == $result['type_id']) { print "</strong>"; }
												}
												*/
											?>
										</div>
									</div>
									
									<div class="search_result_description">
										
									</div>
								</div>
							</a></div>
						<?php endforeach; ?>
					</div>
					
					<?php if(api::tpl()->num_pages > 1): ?>
					<!-- Pagination -->
					<div id="search_pagination">
						<?php
							/*
							$num_each_side = 4;
							
							if(api::tpl()->page > 1) {
								print "<a href=\"". search_url($search['query'], 1, $search['entity_type']) ."\" class=\"page_iteration page_iteration_first ajax\">&laquo; First</a> ";
								print "<a href=\"". search_url($search['query'], ($search['page'] - 1), $search['entity_type']) ."\" class=\"page_iteration page_iteration_previous ajax\">&lsaquo; Prev</a> ";
								
								for($i = max(1, ($search['page'] - $num_each_side)); $i < $search['page']; $i++) {
									print "<a href=\"". search_url($search['query'], $i, $search['entity_type']) ."\" class=\"page_iteration ajax\">". number_format($i) ."</a> ";
								}
							}
							
							print "<a href=\"". search_url($search['query'], $search['page'], $search['entity_type']) ."\" class=\"page_iteration page_iteration_active ajax\">". number_format($search['page']) ."</a> ";
							
							if($search['page'] < $search['num_pages']) {
								for($i = ($search['page'] + 1); $i <= min($search['num_pages'], ($search['page'] + $num_each_side)); $i++) {
									print "<a href=\"". search_url($search['query'], $i, $search['entity_type']) ."\" class=\"page_iteration ajax\">". number_format($i) ."</a> ";
								}
								
								print "<a href=\"". search_url($search['query'], ($search['page'] + 1), $search['entity_type']) ."\" class=\"page_iteration page_iteration_next ajax\">Next &rsaquo;</a> ";
								print "<a href=\"". search_url($search['query'], $search['num_pages'], $search['entity_type']) ."\" class=\"page_iteration page_iteration_last ajax\">Last &raquo;</a> ";
							}
							*/
						?>
					</div>
					<?php endif; ?>
				</div>
				<?php else: ?>
					<p><i>Unfortunately we couldn't find what you were looking for...</i></p>
				<?php endif; ?>
			</div>
		</div>
	</div>
	
<?php api::tpl()->view('footer'); ?>