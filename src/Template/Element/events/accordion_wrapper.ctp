<div id="calendar_list_view_wrapper">
	<div class="event_accordion" id="event_accordion">
		<?php if (empty($events)): ?>
			<p class="no_events alert alert-info">
				No upcoming events found.
			</p>
		<?php else: ?>
			<?php echo $this->element('events/accordion', array('events' => $events)); ?>
		<?php endif; ?>
	</div>
	<?php if (! empty($events)): ?>
		<div id="event_accordion_loading_indicator" style="display: none;">
			<img id="" src="/img/loading_small.gif" /> Loading...
		</div>
		<div id="load_more_events_wrapper">
			<a href="#" id="load_more_events">More events...</a>
		</div>
		<?php $this->Js->buffer("
			$('#load_more_events').button().click(function(event) {
				event.preventDefault();
				loadMoreEvents();
			});
		"); ?>
	<?php endif; ?>
</div>
