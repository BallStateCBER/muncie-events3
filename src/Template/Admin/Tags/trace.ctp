<?php
/**
 * @var \App\View\AppView $this
 */
?>
<b>Trace results:</b><br />
<ul>
	<li>Tags</li>
	<?php foreach ($path as $tag): ?>
		<li>&rarr; <?php echo $tag; ?></li>
	<?php endforeach; ?>
</ul>