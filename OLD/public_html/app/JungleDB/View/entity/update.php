<?php
	if(!empty($_REQUEST['do']) && ($_REQUEST['do'] === "update_entity")) {
		// update specified entity
		$db->update("entity", array(
			'title' => trim($site['args']['entity_title']),
			'excerpt' => trim($site['args']['entity_excerpt']),
		), array('id' => $entity['id']));
		
		$entity = $db->get_row("SELECT * FROM `entity` WHERE `id` = '". $db->escape($entity['id']) ."'");
		
		$deleted_meta_ids = array();
		
		if(!empty($site['args']['entity_meta_delete'])) {
			foreach($site['args']['entity_meta_delete'] as $delete_meta_id) {
				if(!empty($delete_meta_id)) {
					$num_deleted = $db->query("
						DELETE FROM `entity_meta`
						WHERE
							`id` = '". $db->escape($delete_meta_id) ."'
							AND `entity_id` = '". $db->escape($entity['id']) ."'
					");
					
					if(!empty($num_deleted)) {
						$deleted_meta_ids[] = $delete_meta_id;
					}
				}
			}
		}
		
		
		if(!empty($site['args']['entity_meta_key'])) {
			foreach($site['args']['entity_meta_key'] as $update_meta_id => $update_meta_key) {
				if(in_array($update_meta_id, $deleted_meta_ids)) {
					continue;
				}
				
				$update_meta_key = trim($update_meta_key);
				$update_meta_value = trim($site['args']['entity_meta_value'][ $update_meta_id ]);
				
				if(isset($meta[$update_meta_key]['id']) && !empty($update_meta_id) && (strlen($update_meta_key) > 0) && (strlen($update_meta_value) > 0)) {
					$db->update("entity_meta", array(
						'meta_id' => $meta[$update_meta_key]['id'],
						'value' => $update_meta_value,
					), array('id' => $update_meta_id));
				}
			}
		}
		
		
		if(!empty($site['args']['entity_meta_new_key'])) {
			foreach($site['args']['entity_meta_new_key'] as $new_meta_id => $new_meta_key) {
				$new_meta_key = trim(strtolower($new_meta_key));
				$new_meta_value = trim($site['args']['entity_meta_new_value'][ $new_meta_id ]);
				
				if(isset($meta[$new_meta_key]['id']) && (strlen($new_meta_key) > 0) && (strlen($new_meta_value) > 0)) {
					$db->insert("entity_meta", array(
						'entity_id' => $entity['id'],
						'meta_id' => $meta[$new_meta_key]['id'],
						'value' => $new_meta_value,
					));
				}
			}
		}
		
		
		// refresh page
		
		print "<h2 class=\"section_title\">Changes saved...</h2>";
		print "<p>Redirecting you now... or, <a href=\"/j/". $entity['id'] ."/update.html\">go back</a>.</p>\n";
		print "<meta http-equiv=\"refresh\" content=\"1;/j/". $entity['id'] ."/update.html\">\n";
		
		die;
	}
	
	function showEntityTypeParentBreadcrumb_forIndex($entity_type) {
		$entity_types = getEntityTypes();
		
		if(!empty($entity_type['parent_id'])) {
			print showEntityTypeParentBreadcrumb_forIndex($entity_types[ $entity_type['parent_id'] ]) ." &rsaquo; ";
		}
		
		return " ". esc_html($entity_type['title']) ." ";
	}
?>

<h2 class="section_title">Update Entity</h2>

<form method="post" action="/j/<?=esc_attr($entity['id']);?>/update.html">
<input type="hidden" name="do" value="update_entity" />

	<h3 class="sub_title">Entity</h3>
	
	<table class="wiki_table">
		<tbody>
			<tr>
				<td width="150">Type</td>
				<td><?=showEntityTypeParentBreadcrumb_forIndex($entity_type);?></td>
			</tr>
			<tr>
				<td width="150">Title</td>
				<td><input type="text" name="entity_title" value="<?=esc_attr($entity['title']);?>" class="entity_update_text entity_update_text_partial" /></td>
			</tr>
			<tr>
				<td width="150">Excerpt</td>
				<td><textarea name="entity_excerpt" class="entity_update_textarea entity_update_textarea_partial"><?=esc_html($entity['excerpt']);?></textarea></td>
			</tr>
		</tbody>
	</table>
	
	<h3 class="sub_title">Entity Meta</h3>
	<table class="wiki_table">
		<thead>
			<tr>
				<td>Attribute / Value</td>
				<td width="80">Action</td>
			</tr>
		</thead>
		
	<?php if(!empty($entity_meta_db)): foreach($entity_meta_db as $ent_meta): ?>
		<tbody class="entity_meta_update_row" id="entity_meta_update_row_<?=esc_attr($ent_meta['id']);?>">
			<tr>
				<?php /*<td><input type="text" name="entity_meta_key[<?=esc_attr($ent_meta['id']);?>]" value="<?=esc_attr($meta_by_id[$ent_meta['meta_id']]['key']);?>" placeholder="Meta Key" class="entity_update_text" /></td>*/ ?>
				<td><?=esc_html($meta_by_id[$ent_meta['meta_id']]['key']);?><input type="hidden" name="entity_meta_key[<?=esc_attr($ent_meta['id']);?>]" value="<?=esc_attr($meta_by_id[$ent_meta['meta_id']]['key']);?>" /></td>
				<td align="center" valign="center" rowspan="2"><input type="checkbox" name="entity_meta_delete[]" value="<?=esc_attr($ent_meta['id']);?>" class="delete_entity_meta_checkbox" id="entity_meta_delete_<?=esc_attr($ent_meta['id']);?>" data-entity-meta="<?=esc_attr($ent_meta['id']);?>" /><br /><label for="entity_meta_delete_<?=esc_attr($ent_meta['id']);?>">Delete</a></td>
			</tr>
			<tr>
				<td><textarea name="entity_meta_value[<?=esc_attr($ent_meta['id']);?>]" class="entity_update_textarea"><?=esc_html($ent_meta['value']);?></textarea></td>
			</tr>
		</tbody>
		<tr><td colspan="2">&nbsp;</td></tr>
		<?php endforeach; endif; ?>
	</table>
	
	
	<h3 class="sub_title">New Entity Meta</h3>
	<table class="wiki_table">
		<?php for($i = 1; $i <= 5; $i++): ?>
		<tbody class="entity_meta_update_row">
			<tr>
				<td>
					<select name="entity_meta_new_key[<?=esc_attr($i);?>]"<?php /* class="entity_update_text"*/ ?>>
						<?php foreach($meta as $row_meta): ?>
							<option value="<?=esc_attr($row_meta['key']);?>"><?=esc_html($row_meta['key']);?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<td><textarea name="entity_meta_new_value[<?=esc_attr($i);?>]" value="" class="entity_update_textarea" placeholder="Meta Value"></textarea></td>
			</tr>
		</tbody>
		<tr><td colspan="2">&nbsp;</td></tr>
		<?php endfor; ?>
	</table>
	
	<br />
	
	<table class="wiki_table">
		<tbody>
			<tr>
				<td><input type="submit" value="Save Changes" /></td>
			</tr>
		</tbody>
	</table>
	
	<br />
	
	<div class="focus_link">
		<a href="#" class="focus_link_anchor">Delete Entity</a>
		<div class="focus_link_message">Delete entity and everything attached to it</div>
	</div>
</form>

<script type="text/javascript">
<?php ob_start(); ?>
	;jQuery(document).ready(function(e) {
		
		var jungledb_modal_open = function(contents, width, height) {
			contents = contents || "";
			width = width || 400;
			height = height || 300;
			
			if($("#jungledb_modal").length !== 0) {
				$("#jungledb_modal").empty().remove();
			}
			
			$("body").prepend("<div id=\"jungledb_modal\"><div id=\"jungledb_modal_position\"><div id=\"jungledb_modal_inner\">"+ contents +"</div></div></div>");
			
			jungledb_modal_resize(width, height);
			
			/*
			$("<div/>", {
				'id':	"jungledb_modal"
			}).prependTo("body");
			
			$("<div/>", {
				'id':	"jungledb_modal_position"
			}).prependTo("#jungledb_modal");
			
			$("<div/>", {
				'id':	"jungledb_modal_inner"
			}).prependTo("#jungledb_modal_position");
			
			$("#jungledb_modal_inner").empty().css({
				'width':	width,
				'height':	height
			}).append( contents );
			*/
			
			$("#jungledb_modal, #jungledb_modal_position").on('click', jungledb_modal_close);
			
			return true;
		}
		
		var jungledb_modal_resize = function(width, height) {
			if($("#jungledb_modal_inner").length === 0) {
				return false;
			}
			
			$("#jungledb_modal_position").css({
				'height':	(Math.min(height, $("body").innerHeight()))
			})
			
			$("#jungledb_modal_inner").css({
				'width':	width,
				'height':	height
			});
			
			return true;
		}
		
		var jungledb_modal_update = function(contents) {
			if($("#jungledb_modal_inner").length === 0) {
				return jungledb_modal_open(contents);
			}
			
			$("#jungledb_modal_inner").html(contents);
			
			return true;
		}
		
		var jungledb_modal_close = function(e) {
			if(e.target && e.target !== this) {
				return;
			}
			
			try { e.preventDefault(); } catch(Err) {  }
			
			$("#jungledb_modal").empty().remove();
			
			return true;
		}
		
		$(".focus_link_anchor").on('click', function(e) {
			e.preventDefault();
			
			jungledb_modal_open("Hi there!", 500, 1500);
		});
		
		$(":checkbox.delete_entity_meta_checkbox").click(function(e) {
			var meta_id = $(this).attr('data-entity-meta') || false;
			
			if(meta_id) {
				$("#entity_meta_update_row_"+ meta_id).toggleClass("entity_meta_update_row_to_delete", $(this).is(":checked"));
				$("#entity_meta_update_row_"+ meta_id).toggleClass("entity_meta_update_row_to_delete", $(this).is(":checked"));
			}
		});
	});
<?php queue_js_inline( ob_get_contents() ); ob_end_clean(); ?>
</script>