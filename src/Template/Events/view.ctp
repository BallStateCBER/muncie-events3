<h1 class="page_title">
	<?php echo $event['title']; ?>
</h1>

<div class="event">
	<?php
		echo $this->element('events/actions', compact('event'));
		$this->Js->buffer("setupEventActions('.event');");
	?>

	<div class="header_details">
		<table class="details">
			<tr>
				<th>When</th>
				<td>
					<?php echo $this->Calendar->date($event); ?>
					<br />
					<?php echo $this->Calendar->time($event); ?>
				</td>
			</tr>
			<tr>
				<th>Where</th>
				<td>
					<?php echo $this->Html->link(
					   $event['location'],
					   array(
					       'controller' => 'events',
					       'action' => 'location',
					       $event['location']
                       )
                    ); ?>
					<?php if ($event['location_details']): ?>
						<br />
						<?php echo $event['location_details']; ?>
					<?php endif; ?>
					<?php if ($event['address']): ?>
						<br />
						<?php echo $event['address']; ?>
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<th>What</th>
				<td class="what">
					<?php
						echo $this->Html->link(
							$this->Icon->category($event->Category['name']).$event->Category['name'],
							['controller' => 'events', 'action' => 'category', $event->Category['slug']],
							['escape' => false, 'title' => 'View this category']
						);
						if (! empty($event['Tag'])) {
							$linked_tags = [];
							foreach ($event['Tag'] as $tag) {
								$linked_tags[] = $this->Html->link(
									$tag['name'],
									[
										'controller' => 'events',
										'action' => 'tag',
										'slug' => $tag['id'].'_'.Inflector::slug($tag['name'])
									],
									['title' => 'View this tag']
								);
							}
							echo '<span> - '.implode(', ', $linked_tags).'</span>';
						}
					?>
				</td>
			</tr>
			<?php if ($event['cost']): ?>
				<tr>
					<th>Cost</th>
					<td><?php echo $event['cost']; ?></td>
				</tr>
			<?php endif; ?>
			<?php if ($event['age_restriction']): ?>
				<tr>
					<th>Ages</th>
					<td><?php echo $event['age_restriction']; ?></td>
				</tr>
			<?php endif; ?>
			<?php if ($event['series_id'] && $event->EventSeries['title']): ?>
				<tr>
					<th>Series</th>
					<td>
						<?php echo $this->Html->link(
							$event->EventSeries['title'],
							['controller' => 'event_series', 'action' => 'view', 'id' => $event['series_id']]
						); ?>
					</td>
				</tr>
			<?php endif; ?>
		</table>
	</div>
	<div class="description">
		<?php if (! empty($event->EventsImage)): ?>
			<div class="images">
				<?php foreach ($event->EventsImage as $image): ?>
					<?php echo $this->Calendar->thumbnail('small', [
						'filename' => $image->Image['filename'],
						'caption' => $image['caption'],
						'group' => 'event'.$event['id']
					]); ?>
					<?php if ($image['caption']): ?>
						<span class="caption">
							<?php echo $image['caption']; ?>
						</span>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
		<?php echo $this->Text->autoLink($event['description'], [
			'escape' => false
		]); ?>
	</div>

	<div class="footer_details">
		<p>
			<?php if (! $event->User['id']): ?>
				Added anonymously
			<?php elseif (! $event->User['name']): ?>
				Added by a user whose account no longer exists
			<?php else: ?>
				Author: <?php echo $this->Html->link(
					$event->User['name'],
					['controller' => 'users', 'action' => 'view', 'id' => $event->User['id']]
				); ?>
			<?php endif; ?>

			<?php if ($event['source']): ?>
				<br />
				Source:
				<?php echo $this->Text->autoLink($event['source']); ?>
			<?php endif; ?>
		</p>
	</div>
</div>
