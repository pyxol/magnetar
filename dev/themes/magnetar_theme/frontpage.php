<?php
	display_tpl('header', [
		'title' => $this->title ?? 'Frontpage',
	]);
?>
	
	<h1>Magnetar Framework</h1>
	
	<p>Welcome to a fully featured Web Application framework powered by <a href="https://www.github.com/pyxol/magnetar/" target="_blank">Magnetar</a>.</p>
	
<?php
	display_tpl('footer');