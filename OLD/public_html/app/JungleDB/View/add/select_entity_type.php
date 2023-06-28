<?php
	$entity_types = getEntityTypes_sortedByParentID();
	$entity_types_by_id = getEntityTypes();
	
	// selected parent type
	if(!empty($site['args']['id']) && array_key_exists($site['args']['id'], $entity_types_by_id) && ($entity_types_by_id[ $site['args']['id'] ]['parent_id'] == "0")) {
		$entity_type = $entity_types_by_id[ $site['args']['id'] ];
	}
	
	function selectEntityType_childList($entity_type, $depth = 0) {
		if(empty($entity_type['id']) || $entity_type['display_always'] == "0") {
			return;
		}
		
		$entity_types = getEntityTypes_sortedByParentID();
		
		print "<ul class=\"ae_et_children\">\n";
		
		if($depth === 0 && !empty($entity_type['can_entity'])) {
			print "<li>\n";
			print "	<div class=\"ae_etc_title\"><a href=\"/add/new_entity/?id=". $entity_type['id'] ."\" title=\"". esc_attr($entity_type['title'])  ."\">". esc_html($entity_type['title']) ."</a></div>\n";
			print "</li>\n";
		}
		
		if(!empty($entity_types[$entity_type['id']])) {
			foreach($entity_types[ $entity_type['id'] ] as $et_child) {
				print "<li>\n";
				
				print "<div class=\"ae_etc_title\">\n";
				if(!empty($et_child['can_entity'])) {
					// can have entities
					//print "<a href=\"/add/search_by_title/?id=". $et_child['id'] ."\" title=\"Add ". esc_attr($et_child['title'])  ." Entity\">". esc_html($et_child['title']) ."</a>";
					print "<a href=\"/add/new_entity/?id=". $et_child['id'] ."\" title=\"Add ". esc_attr($et_child['title'])  ." Entity\">". esc_html($et_child['title']) ."</a>";
				} else {
					// can't have entities
					print "<strong>". esc_html($et_child['title']) ."</strong>";
				}
				print "</div>\n";
				
				if(!empty($entity_types[$et_child['id']])) {
					selectEntityType_childList($et_child, (1 + $depth));
				}
				
				print "</li>\n";
			}
		}
		
		print "</ul>\n";
	}
?>

<div class="add_entity_pretitle"><a href="/add/">Add an Entity</a> &rsaquo; Select an Entity Type</div>
<h2 class="add_entity_title">Add an Entity</h2>

<?php if(!empty($entity_types[0])): ?>
<div class="ae_et_sidebar">
	<ul class="ae_et_list">
		<?php foreach($entity_types[0] as $parent_entity_type): if($parent_entity_type['display_always'] == "0") { continue; } ?>
		<li<?=(isset($site['args']['id']) && $site['args']['id'] == $parent_entity_type['id']?" class=\"ae_et_active\"":"");?>><a href="/add/select_entity_type/?id=<?=$parent_entity_type['id'];?>" title="<?=esc_attr($parent_entity_type['title']);?>" class="ae_et_anchor">
			<h2 class="ae_et_parent"><?=esc_html($parent_entity_type['title']);?></h2>
		</a></li>
		<?php endforeach; ?>
	</ul>
</div>

<div class="ae_et_content">
	<?php if(!empty($entity_type)): ?>
		<?=selectEntityType_childList($entity_type);?>
	<?php else: ?>
		<p class="error_soft">Please select a category on the left</p>
	<?php endif; ?>
</div>

<?php else: ?>
<p class="error_soft">Error #1001: Adding an entity has been temporarily disabled</p>
<?php endif; ?>

<style type="text/css">
	.ae_et_sidebar { float: left; width: 150px; }
	
		.ae_et_list { display: block; width: 150px; margin: 0 0 15px 0; padding: 0; }
			.ae_et_list li { width: 150px; height: 60px; background: #f0f0f0; margin: 0 0 15px 0; overflow: hidden;
				-webkit-border-bottom-left-radius: 7px;
				-moz-border-radius-bottomleft: 7px;
				border-bottom-left-radius: 7px;
			}
				.ae_et_list li .ae_et_anchor { display: block; width: 150px; height: 60px; text-align: center; outline: hidden; cursor: pointer; }
					.ae_et_list li .ae_et_anchor .ae_et_parent { display: inline-block; margin: 0 auto; font: normal normal bold 16px/16px Verdana; color: #555; text-decoration: none; border-bottom: 2px solid transparent; padding-top: 23px; }
				.ae_et_list li.ae_et_active { background: #fafafa;  }
				.ae_et_list li:hover { cursor: pointer; }
					.ae_et_list li:hover .ae_et_anchor .ae_et_parent, .ae_et_list li.ae_et_active .ae_et_anchor .ae_et_parent { text-decoration: none; border-color: #3366CC; }
	
	.ae_et_content { float: right; width: 840px; text-align: left; }
		.ae_et_children { display: block; padding: 5px; margin-bottom: 5px; }
			.ae_et_children li { padding-top: 5px; }
				.ae_et_children li:first-child { padding-top: 0; }
				.ae_et_children .ae_etc_title { margin-bottom: 5px; }
				.ae_et_children li a { font: normal normal normal 18px/18px Verdana; color: #3366CC; text-decoration: none; }
					.ae_et_children li a:hover { text-decoration: underline; }
				.ae_et_children li strong { font: normal normal normal 18px/18px Verdana; color: #555; }
				.ae_et_children li .ae_et_children { margin-left: 10px; border-left: 2px solid #f0f0f0; padding: 0 0 0 10px; }
</style>

<script type="text/javascript">
<?php ob_start(); ?>
	;jQuery(document).ready(function($) {
		$("body").on('click', ".entity_type_parent", function(e) {
			e.preventDefault();
		});
	});
<?php queue_js_inline( ob_get_contents() ); ob_end_clean(); ?>
</script>