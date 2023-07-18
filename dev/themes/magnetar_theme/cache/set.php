<?php
	display_tpl('header', [
		'title' => 'Cache / Set',
	]);
?>
	
	<h1>Cache / Set</h1>
	
	<div class="alert alert-warning">
		Cache expires in 15 seconds
	</div>
	
	<pre><?=esc_html(print_r([
		'cached_val' => $this->cached_val,
		'cache_set' => $this->cache_set,
	], true));?></pre>
	
	<h2 class="h4">Debug Log</h2>
	
	<pre><?=$this->log;?></pre>
	
<?php
	display_tpl('footer');