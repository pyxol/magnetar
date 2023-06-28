<?php
	$default_action = "select_entity_type";
	
	$actions = array(
		"select_entity_type",
		"search_by_title",
		"new_entity",
		"create_entity",
	);
	
	$action = ((!empty($site['args']['action']) && in_array($site['args']['action'], $actions))?$site['args']['action']:$default_action);
	$action_tpl_path = $site['dir']['template_path'] ."add". DS . $action .".php";
	
	function showEntityTypeParentBreadcrumb($entity_type, $link=true) {
		$entity_types = getEntityTypes();
		
		if(!empty($entity_type['parent_id'])) {
			print showEntityTypeParentBreadcrumb($entity_types[ $entity_type['parent_id'] ], $link) ." &rsaquo; ";
		}
		
		if(!empty($link) && !empty($entity_type['can_entity'])) {
			return " <a href=\"/add/new_entity/?id=". esc_attr($entity_type['id']) ."\">". esc_html($entity_type['title']) ."</a> ";
		}
		
		return " ". esc_html($entity_type['title']) ." ";
	}
	
	get_header();
?>
	
	<div id="app" class="one-sided">
		<div id="main">
			<?php
				if(file_exists($action_tpl_path)) {
					include($action_tpl_path);
				} else {
					// display 404 page
					
					print "<p class=\"error_soft\">The suggested template file 'add/". html_entity_decode($action, ENT_COMPAT, 'UTF-8') .".php' could not be found.</p>";
				}
			?>
		</div>
	</div>
	
	<style type="text/css">
		.add_entity_pretitle { margin: 0 0 10px 0; font: normal normal normal 11px/11px Verdana; color: #505050; }
			.add_entity_pretitle a { font: normal normal normal 11px/11px Verdana; color: #505050; text-decoration: none; border-bottom: 1px solid #c0c0c0; }
				.add_entity_pretitle a:hover { border-color: #3366CC; }
		.add_entity_title { font: normal normal bold 18px/18px Verdana; color: #303030; margin: 0 0 15px 0; }
		
		.error_soft { text-align: center; padding: 100px 0; font: normal italic normal 12px/12px Verdana; color: #c0c0c0; text-transform: lowercase; }
	</style>
	
<?php
	get_footer();