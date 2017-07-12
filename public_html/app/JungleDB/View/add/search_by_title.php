<?php
	if(empty($site['args']['id'])) {
		redirect("/add/");
		
		die;
	}
	
	$entity_types = getEntityTypes();
	
	if(!array_key_exists($site['args']['id'], $entity_types)) {
		redirect("/add/");
		
		die;
	}
	
	$entity_type = $entity_types[ $site['args']['id'] ];
?>

<div class="add_entity_pretitle"><a href="/add/">Add an Entity</a> &rsaquo; <?=showEntityTypeParentBreadcrumb($entity_type);?> &rsaquo; Confirm JungleDB Entity Doesn't Exist</div>
<h2 class="add_entity_title">Add an Entity</h2>

<?php /*<p>Search our existing pool of <strong><?=(!empty($entity_type['parent_id'])?esc_html($entity_types[ $entity_type['parent_id'] ]['title']) ." ":"") . esc_html($entity_type['title']);?></strong> entities with the tool below and confirm it doesn't already exist before continuing.</p>*/ ?>
<p>Search our existing pool of <strong><?=esc_html($entity_type['title']);?></strong> entities with the tool below and confirm it doesn't already exist before continuing.</p>

<div class="add_entity_search">
	<form id="entity_title_search_form" action="/search.php">
		<input type="text" name="q" value="" placeholder="Enter the title/name of your new entry" class="entity_title_search_text" autocomplete="off" /> <input type="submit" value="Search" class="button_submit entity_title_search_submit" />
	</form>
</div>

<div class="add_entity_search_container">
	<div class="add_entity_confirm_container">
		<a href="#" class="add_entity_confirm_anchor" data-entity-type="<?=esc_attr($entity_type['id']);?>">I Confirm the <?=esc_html($entity_type['title']);?> Entity &quot;<strong id="add_entity_confirm_title"></strong>&quot; Doesn't Exist on JungleDB</a>
	</div>
	
	<div id="search_results" class="add_entity_search_results"></div>
	
	<p id="ae_sr_message" class="error_soft"></p>
</div>

<style type="text/css">
	.add_entity_confirm_container { margin-bottom: 15px; text-align: center; }
		.add_entity_confirm_anchor { font: normal normal normal 24px/24px Verdana; color: #3366CC; }
	.add_entity_search { width: 850px; margin: 0 auto 15px auto; }
		.add_entity_search .entity_title_search_text { vertical-align: text-bottom; border: 1px solid #c0c0c0; width: 500px !important; padding: 6px 12px; width: 500px; font: normal normal normal 14px/14px Verdana; color: #303030; outline: none;
			-webkit-border-bottom-left-radius: 5px;
			-moz-border-radius-bottomleft: 5px;
			border-bottom-left-radius: 5px;
		}
	
	.add_entity_search_container { display: none; }
</style>

<script type="text/javascript">
<?php ob_start(); ?>
	;jQuery(document).ready(function($) {
		var callSearchResults = function() {
			JungleDB.Actions.search_ajaxResults({
				'query':			$(".entity_title_search_text").val(),
				'entity_type': 		"<?=esc_attr($entity_type['id']);?>",
				'results_element':	"#search_results",
				'message_element':	"#ae_sr_message",
				
				'before':			function(query) {
					$(".add_entity_search_container").hide();
					$(".add_entity_confirm_anchor").attr({'href': ""});
					$("#add_entity_confirm_title").empty();
				},
				
				'after':			function(query) {
					$(".add_entity_confirm_anchor").attr({
						'href': "/add/new_entity/?id=<?=esc_attr($entity_type['id']);?>&title="+ query.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;")
					});
					
					$("#add_entity_confirm_title").text( query );
					
					$(".add_entity_search_container").show();
				} 
			});
		};
		
		$("#entity_title_search_form").on('submit', function(e) {
			e.preventDefault();
			
			callSearchResults();
		});
		
		//$(".entity_title_search_text").on('keyup', function(e) {
		//	e.preventDefault();
		//	
		//	callSearchResults();
		//});
		
	});
<?php queue_js_inline( ob_get_contents() ); ob_end_clean(); ?>
</script>