<h1 class="page_title">
	<?php echo $title_for_layout; ?>
</h1>

<div class="my_events">
	<?php if (empty($events) && empty($series)): ?>
		You don't have any events. Care to 
		<?php echo $this->Html->link(
			'submit one', 
			array('controller' => 'events', 'action' => 'add')
		); ?>?
	<?php else: ?>
		<h1>Event Series</h1>
		<table class="my_content">
			<?php foreach ($series as $key => $a_series): $id = $a_series['EventSeries']['id']; ?>
				<tr<?php if ($key % 2 == 1): ?> class="altrow"<?php endif; ?>>
					<td class="date">
						<span class="fake_link" onclick="$('myeventseries_<?php echo $id; ?>_events').toggle()">
							Expand
						</span>
					</td>
					<th>
						<?php echo $this->Html->link(
							$a_series['EventSeries']['title'], 
							array('controller' => 'event_series', 'action' => 'view', 'id' => $id)
						); ?> 
					</th>
					<td>
						<?php echo $this->Html->link(
							$this->Html->image(
								'/img/icons/fugue/icons/magnifier.png', 
								array('title' => 'View', 'alt' => 'View')
							).' View',
							array('controller' => 'event_series', 'action' => 'view', 'id' => $id), 
							array('escape' => false)
						); ?>
					</td>
					<td>
						<?php echo $this->Html->link(
							$this->Html->image(
								'/img/icons/fugue/icons/pencil.png', 
								array('title' => 'Edit', 'alt' => 'Edit')
							).' Edit', 
							array('controller' => 'event_series', 'action' => 'edit', 'id' => $id),
							array('escape' => false)
						); ?>
					</td>
					<td>
						<?php echo $this->Html->link(
							$this->Html->image(
								'/img/icons/fugue/icons/cross.png', 
								array('title' => 'Delete', 'alt' => 'Delete')
							).' Delete', 
							array('controller' => 'event_series', 'action' => 'delete', 'id' => $id),
							array('escape' => false),
							'Are you sure you want to remove this event series? All of its events will be permanently deleted.'
						); ?>
					</td>
				</tr>
				<tr id="myeventseries_<?php echo $id; ?>_events" style="display: none;">
					<td class="expanded">
						<?php echo $this->Html->image('icons/arrow-turn-000-left.png'); ?>
					</td>
					<td colspan="4" class="myeventseries_events">
						<?php echo $this->element('events/my_events', array('events' => $a_series['Event'])); ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</table>
	
		<h1>Single Events</h1>
		<?php echo $this->element('events/my_events', array('events' => $events)); ?>
	<?php endif; ?>
</div>