<?php
	use Cake\Routing\Router;
	$leave_open = (isset($open_only_event) && $open_only_event && count($events) == 1);
?>

	<ul class="event_accordion">
		<?php foreach ($events as $event): ?>
		<li <?php echo (! empty($event['EventsImage'])) ? 'class="with_images"' : ''; ?>>
			<?php
				$url = Router::url([
					'controller' => 'events',
					'action' => 'view',
					'id' => $event['id']
				], true);
			?>
			<?php if (! empty($event['EventsImage'])): ?>
				<span class="tiny_thumbnails">
					<?php
						foreach ($event['EventsImage'] as $image) {
							echo $this->Calendar->thumbnail('tiny', [
								'filename' => $image->Image['filename'],
								'caption' => $image['caption'],
								'group' => 'event'.$event['id'].'_tiny_tn'
							]);
						}
					?>
				</span>
				<?php endif; ?>
				<a data-toggle="collapse" data-target="#more_info_<?php echo $event['id']; ?>" href="<?php echo $url; ?>" title="Click for more info" class="more_info_handle" id="more_info_handle_<?php echo $event['id']; ?>" data-event-id="<?php echo $event['id']; ?>">
					<?php echo $this->Icon->category($event->Category['name']); ?>
					<span class="title">
					<?php echo $event['title']; ?>
				</span>
					<span class="when">
					<?php echo $this->Calendar->eventTime($event); ?>
					@
				</span>
					<span class="where">
					<?php echo $event['location'] ? $event['location'] : '&nbsp;'; ?>
					<div class="collapse" id="more_info_<?php echo $event['id']; ?>" <?php if (! $leave_open): ?>style="height: 0;"<?php endif; ?>>
						<?php if ($event['location_details']): ?>
							<span class="location_details">
								<?php echo $event['location_details']; ?>
							</span>
					<?php endif; ?>
					<?php if ($event['address']): ?>
					<span class="address" id="address_<?php echo $event['id']; ?>">
								<?php echo $event['address']; ?>
							</span>
					<?php endif; ?>
					</span>
				</a>
				<div class="card">
					<div class="card-header">
						<?php echo $this->element('events/actions', compact('event')); ?>
					</div>
					<div class="description">
						<?php if (! empty($event['EventsImage'])): ?>
						<div class="images">
							<?php foreach ($event['EventsImage'] as $image): ?>
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
						<?php if ($event['description']): ?>
						<?php echo $this->Text->autolink($event['description'], ['escape' => false]); ?>
						<?php endif; ?>
						<?php if ($event['cost'] || $event['age_restriction']): ?>
						<div class="details">
							<table>
								<?php if ($event['cost']): ?>
								<tr class="cost">
									<th>Cost:</th>
									<td>
										<?php echo $event['cost']; ?>
									</td>
								</tr>
								<?php endif; ?>
								<?php if ($event['age_restriction']): ?>
								<tr class="age_restriction detail" id="age_restriction_<?php echo $event['id']; ?>">
									<th>Ages:</th>
									<td>
										<?php echo $event['age_restriction']; ?>
									</td>
								</tr>
								<?php endif; ?>
							</table>
						</div>
						<?php endif; ?>
					</div>
					<div class="card-footer">
						<table class="details">
							<?php if (! empty($event['Tag'])): ?>
							<tr class="tags">
								<th>Tags:</th>
								<td>
									<?php echo $this->Calendar->eventTags($event); ?>
								</td>
							</tr>
							<?php endif; ?>
							<?php if (! empty($event['series_id']) && ! empty($event->EventSeries['title'])): ?>
							<tr class="tags">
								<th>Series:</th>
								<td>
									<?php echo $this->Html->link($event->EventSeries['title'], [
												'controller' => 'event_series',
												'action' => 'view',
												'id' => $event->EventSeries['id']
											]); ?>
								</td>
							</tr>
							<?php endif; ?>
							<?php if ($event['source']): ?>
							<tr class="source">
								<th>Source:</th>
								<td>
									<?php echo $this->Text->autoLink($event['source']); ?>
								</td>
							</tr>
							<?php endif; ?>
							<tr class="link">
								<th>Link:</th>
								<td>
									<?php echo $this->Html->link($url, $url); ?>
								</td>
							</tr>
							<?php if (isset($event->User['name']) && $event->User['name']): ?>
							<tr class="author">
								<th>
									Author:
								</th>
								<td>
									<?php echo $this->Html->link($event->User['name'],
												['controller' => 'users', 'action' => 'view', 'id' => $event->User['id']]
											 ); ?>
								</td>
							</tr>
							<?php endif; ?>
						</table>
					</div>
				</div>
				</div>
		</li>
		<?php endforeach; ?>
	</ul>
	<?php
	if ($leave_open) {
		$this->Js->buffer("
			$('.event_accordion a.tn_tiny').hide();
		");
	}
?>
