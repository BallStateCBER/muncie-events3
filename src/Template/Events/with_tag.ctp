<h3 class="selected_tag_category">
	Events
</h3>

<?php
$this->Paginator->__defaultModel = 'Event';
	$this->Paginator->options = array(
		'model' => 'Event',
		'update' => 'tagged_content_links_loading',
		'url' => array(
			'controller' => 'events',
			'action' => 'with_tag',
			$tag_id
		),
		'evalScripts' => true
	);
?>
<?php echo $this->element('paging', array('model' => 'Event', 'options' => array('numbers' => true))); ?>
<?php if (empty($events)): ?>
	Strange. No events have been tagged with this tag. How did you get here?
<?php else: ?>
	<table class="events_list">
		<?php $i = 0; ?>
		<?php foreach ($events as $id => $event) : ?>
			<tr<?php if ($i % 2 == 1): ?> class="shaded"<?php endif; ?>>
				<td class="date"> 
					<?php echo date('M j, Y', strtotime($event['Event']['date'])); ?> 
				</td>
				<th class="title">
					<?php echo $this->Html->link(
						$event['Event']['title'], 
						array('controller' => 'events', 'action' => 'view', 'id' => $event['Event']['id'])
					); ?> 
				</th>
			</tr>
		<?php $i++; endforeach; ?>
	</table>
<?php endif; ?>