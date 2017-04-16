<ul class="auto_complete">
	<?php if (! empty($tags)): ?>
		<?php foreach($tags as $tag): ?>
			<li><?php echo $tag; ?></li>
		<?php endforeach; ?>
	<?php endif; ?>
</ul>