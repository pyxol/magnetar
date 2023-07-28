<?php
	display_tpl('header', [
		'title' => $this->title ?? 'Frontpage',
	]);
?>
	
	<h1>Magnetar Framework</h1>
	
	<p>Welcome to a fully featured Web Application framework powered by <a href="https://www.github.com/pyxol/magnetar/" title="PHP Dependency Injection and Routing Framework" target="_blank">Magnetar</a>. For an easy start, the starter application <a href="https://www.github.com/pyxol/pulsar/" title="PHP Web Application Framework" target="_blank">Pulsar</a> is recommended.</p>
	
	<p><a href="https://github.com/pyxol/pulsar/" target="_blank" title="PHP Web Application Framework" class="btn btn-primary"><strong>Try Pulsar</strong></a></p>
	
<?php
	display_tpl('footer');