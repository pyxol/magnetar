<?php
	display_tpl('header', [
		'title' => 'Cache / Get',
	]);
?>
	
	<h1>Cache / Get</h1>
	
	<pre><?=esc_html(print_r([
		'cached_val' => $this->cached_val,
	], true));?></pre>
	
	<h2 class="h4">Debug Log</h2>
	
	<pre><?=$this->log;?></pre>
	
	<a href="/cache/set/" class="btn btn-primary">Set Cache</a>
	
<?php
	display_tpl('footer');