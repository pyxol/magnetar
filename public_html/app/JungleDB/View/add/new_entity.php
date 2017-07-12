<?php
	$entity_type_id = (!empty($site['args']['id'])?$site['args']['id']:false);
	$title = (!empty($site['args']['title'])?trim($site['args']['title']):"");
	$excerpt = (!empty($site['args']['excerpt'])?trim($site['args']['excerpt']):"");
	
	if(empty($entity_type_id)) {
		redirect("/add/");
		
		die;
	}
	
	$entity_types = getEntityTypes();
	
	//if(!array_key_exists($entity_type_id, $entity_types) || empty($entity_types[ $entity_type_id ]['can_entity']))
	if(empty($entity_types[ $entity_type_id ]['can_entity'])) {
		redirect("/add/#=add_entity_type_not_found=");
		
		die;
	}
	
	$entity_type = $entity_types[ $entity_type_id ];
?>

<div class="add_entity_pretitle"><a href="/add/">Add an Entity</a> &rsaquo; <?=showEntityTypeParentBreadcrumb($entity_type);?> &rsaquo; New Entity</div>
<h2 class="add_entity_title">Add an Entity</h2>

<div class="form_container">
	<form method="post" action="/add/create_entity/">
	<input type="hidden" name="entity_type" value="<?=esc_attr($entity_type['id']);?>" />
	
	<div class="form_row">
		<div class="form_row_title">
			<label for="ae_ne_entity_type">Entity Type</label>
		</div>
		<div class="form_row_input">
			<p class="value"><?=showEntityTypeParentBreadcrumb($entity_type, false);?> [<a href="/add/" class="jdb_modal" title="Change Entity Type">change</a>]</p>
		</div>
	</div>
	
	<div class="form_row">
		<div class="form_row_title">
			<label for="ae_ne_title">Title</label>
		</div>
		<div class="form_row_input">
			<input type="text" name="entity_title" id="ae_ne_title" value="<?=esc_attr($title);?>" />
			<div class="alert">
				<p>Provide the <strong>official/legal</strong> name of the specific entity, whether it be:</p>
				
				<ul class="legend">
					<li>legal birth name of a person (<strong>John Fitzgerald Kennedy</strong> for <a href="/j/person/john-f-kennedy/" target="_blank">John F. Kennedy</a>)</li>
					<li>model of a car (<strong>F-150</strong> for a <a href="/j/car/ford-f-150/" target="_blank"><?=date("Y");?> Ford F-150</a>)</li>
					<li>name of a city (<strong>Newark</strong> for <a href="/j/city/newark/" target="_blank">Newark, New Jersey, USA</a>)</li>
					<li><a href="/help/entity/titling.html" target="_blank">more examples</a></li>
				</ul>
			</div>
		</div>
	</div>
	
	<div class="form_row">
		<div class="form_row_title">
			<label for="ae_ne_excerpt">Short Description</label>
			<p>Describe this entity in a sentence or two</p>
			<p><strong>optional</strong></p>
		</div>
		<div class="form_row_input">
			<textarea name="entity_excerpt" id="ae_ne_excerpt"></textarea>
		</div>
	</div>
	
	<div class="form_row">
		<div class="form_row_input">
			<input type="submit" value="Create Entity" class="button_submit" />&nbsp; &nbsp;[<a href="/">Cancel</a>]
		</div>
	</div>
	
	</form>
</div>

<style type="text/css">
	.form_container { width: 870px !important; padding: 15px; width: 900px; margin: 0 auto 15px auto; border: 1px solid #3366CC; background-color: #d3eaff;
		-webkit-border-bottom-left-radius: 5px;
		-moz-border-radius-bottomleft: 5px;
		border-bottom-left-radius: 5px;
	}
		.form_row_spacer { padding-top: 20px; }
		.form_row { display: table; width: 870px; margin: 0 0 10px 0; }
			.form_container .form_row:last-child { margin-bottom: 0; }
			
		.form_row_title { float: left; width: 180px !important; padding: 5px 10px 0 10px; width: 200px; text-align: right; vertical-align: text-bottom; }
			.form_row_title label { display: block; font: normal normal bold 14px/14px Verdana; color: #303030; text-align: right; }
			.form_row_title p { margin-bottom: 0; padding-top: 8px; font: normal normal normal 11px/11px Verdana; color: #505050; text-align: right; }
		.form_row_input { float: right; width: 635px !important; padding: 0 10px 0 10px; width: 655px; }
		
		.form_row_input input[type='text'], .form_row_input textarea {
			-webkit-border-bottom-left-radius: 5px;
			-moz-border-radius-bottomleft: 5px;
			border-bottom-left-radius: 5px;
		}
		
		.form_row_input p { margin-bottom: 0; padding-top: 5px; font: normal normal normal 12px/14px Verdana; color: #505050; vertical-align: text-bottom; }
			.form_row_input p.value { font: normal normal normal 14px/16px Verdana; color: #505050; }
			.form_row_input .alert { padding-left: 30px; background: transparent url('/templates/jungle/static/images/icons/alert_16.png') no-repeat 5px 5px; }
			.form_row_input .legend { margin: 0 0 0 15px; padding: 10px 0 5px 15px; list-style-type: disc; }
				.form_row_input .legend li { margin: 0 0 5px 0; }
		.form_row_input input[type='text'] { font: normal normal normal 14px/14px Verdana; color: #505050; width: 580px !important; padding: 4px 10px; width: 600px; border: 1px solid #c0c0c0; }
		.form_row_input textarea { font: normal normal normal 14px/14px Verdana; color: #505050; height: 60px !important; width: 580px !important; padding: 10px 10px; width: 600px; height: 68px; border: 1px solid #c0c0c0; }
			.form_row_input textarea.long { height: 500px !important; height: 520px; }
</style>

<script type="text/javascript">
<?php ob_start(); ?>
	;jQuery(document).ready(function($) {
		
	});
<?php queue_js_inline( ob_get_contents() ); ob_end_clean(); ?>
</script>