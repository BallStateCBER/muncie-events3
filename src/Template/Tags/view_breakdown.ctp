<?php
$model_plural = Inflector::pluralize($model);
$model_lower_plural = strtolower($model_plural);
?>
<h3 class="selected_tag_category">
	<?php echo ucwords(Inflector::humanize($model_plural)); ?>
</h3>
<?php if (empty($tagged_items)): ?>
	Strange. No <?php echo $model_lower_plural ?> have been tagged with this tag. How did you get here?
<?php else: ?>
	<?php if ($model == 'Flyer'): ?>
		<div class="flyers">
			<?php foreach ($tagged_items as $id => $flyer): ?>
				<div class="flyer_thumbnail">
					<a href="<? $fullBaseUrl ?>/img/flyers/<?php echo $flyer['full_filename']; ?>" rel="shadowbox" class="flyer_thumbnail">
						<img
							src="/<? $fullBaseUrl ?>img/flyers/<?php echo $flyer['thumbnail_filename']; ?>"
							class="flyer_thumbnail"
							title="Click to view full-sized"
						/>
					</a>
				</div>
			<?php endforeach; ?>
			<br class="clear" />
		</div>
	<?php else: ?>
		<ul class="items">
			<?php foreach ($tagged_items as $id => $title): ?>
				<li>
					<?php echo $this->Html->link(
                        $title,
                        array('controller' => $model_lower_plural, 'action' => 'view', 'id' => $id)); ?>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
<?php endif; ?>
