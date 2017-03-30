<h1 class="page_title">
	<?php
		$total = $this->Paginator->counter(array('format' => '{:count}'));
		echo "$total ";
		if ($direction == 'future') {
			echo 'Upcoming ';
		} elseif ($direction == 'past') {
			echo 'Past ';
		}
		echo __n('Event', 'Events', $total)." at $location";
	?>
</h1>

<?php if ($count_other_direction !== false): ?>
	<?php if ($count_other_direction): ?>
		<?php
			$link_text = $count_other_direction.' ';
			$link_text .= ($direction == 'future') ? 'past' : 'upcoming';
			$link_text .= __n(' event', ' events', $count_other_direction);
			echo $this->Html->link($link_text, array(
				'controller' => 'events',
				'action' => 'location',
				'location' => $location,
				'direction' => ($direction == 'future') ? 'past' : 'future'
			));
		?>
		at <?php echo $location; ?>
	<?php else: ?>
		<p class="light_text">
			There are no <?php echo (($direction == 'future') ? 'past' : 'upcoming'); ?> events at <?php echo $location; ?>
		</p>
	<?php endif; ?>
<?php endif; ?>

<?php if (isset($events) && ! empty($events)): ?>

	<?php echo $this->element('pagination'); ?>

	<?php foreach ($events as $date => $days_events): ?>
		<?php echo $this->Calendar->dayHeaders($date); ?>
		<?php echo $this->element('events/accordion_day', array(
			'events' => $days_events
		)); ?>
	<?php endforeach; ?>

	<?php echo $this->element('pagination'); ?>

	<?php $this->Js->buffer("setupEventAccordion();"); ?>

<?php else: ?>
	<p class="alert alert-info">
		No events found.
	</p>
<?php endif; ?>
