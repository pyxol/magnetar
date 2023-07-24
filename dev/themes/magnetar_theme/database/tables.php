<?php
	display_tpl('header', [
		'title' => 'Database Tables',
	]);
?>
	
	<h1>Database</h1>
	
	<div class="row">
		<div class="col-sm-3">
			<ul>
				<?php foreach($this->tables as $table): ?>
					<li>
						<a href="<?=esc_attr('/db/?table='. urlencode($table));?>">
							<?=esc_html($table);?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
		<div class="col-sm-9">
			<?php if($this->table): ?>
				<h2><?=esc_html($this->table);?></h2>
				
				<?php $columns = array_keys($this->rows[0]); ?>
				
				<table class="table">
					<thead>
						<tr>
							<?php foreach($columns as $column): ?>
								<th><?=esc_html($column);?></th>
							<?php endforeach; ?>
						</tr>
					</thead>
					<tbody>
						<?php foreach($this->rows as $row): ?>
							<tr>
								<?php foreach($columns as $column): $value = $row[ $column ] ?? null; ?>
									<?php if(null !== $value): ?>
										<td><?=esc_html($value);?></td>
									<?php else: ?>
										<td class="text-muted"><em>NULL</em></td>
									<?php endif; ?>
								<?php endforeach; ?>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php else: ?>
				<p><em>Select a table to preview rows</em></p>
			<?php endif; ?>
		</div>
	</div>
	
<?php
	display_tpl('footer');