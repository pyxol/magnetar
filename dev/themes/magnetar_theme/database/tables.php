<?php
	display_tpl('header', [
		'title' => 'Database Tables',
	]);
?>
	
	<h1>Database Tables</h1>
	
	<pre><?=esc_html(print_r($this->tables, true));?></pre>
	
<?php
	display_tpl('footer');