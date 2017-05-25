<div id="calendar_list_view_wrapper">
    <div class="event_accordion" id="event_accordion">
        <?php if (empty($events)): ?>
            <p class="no_events alert alert-info" id="no_events">
                No events found.
            </p>
        <?php else: ?>
            <?= $this->element('events/accordion', ['events' => $events]); ?>
        <?php endif; ?>
    </div>
</div>
