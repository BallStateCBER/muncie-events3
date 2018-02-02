<?php
/*
    This displays complete information for a collection of events.
    $events can be for multiple days, i.e.
        $events[$date][$k] = $event
    or one day, i.e.
        $events[$k] = $event
*/
?>

<?php if (empty($events)): ?>
    <?php $this->Js->buffer("setNoMoreEvents();"); ?>
<?php else: ?>
    <?php if (!$multipleDates): ?>
        <?php foreach ($events as $date => $event): ?>
            <?= $this->Calendar->dayHeaders($date) ?>
            <ul class="event_accordion">
                <?= $this->element('events/accordion_day', [
                    'event' => $event
                ]) ?>
            </ul>
        <?php endforeach; ?>
    <?php else: ?>
        <?php foreach ($events as $date => $event): ?>
            <?= $this->Calendar->dayHeaders($date) ?>
            <ul class="event_accordion">
                <?php if (count($event) > 1): ?>
                    <?php foreach ($event as $k => $e): ?>
                        <?= $this->element('events/accordion_day', [
                            'event' => $e
                        ]) ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <?= $this->element('events/accordion_day', [
                        'event' => $event
                    ]) ?>
                <?php endif; ?>
            </ul>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if (isset($nextStartDate)): ?>
        <?php $this->Js->buffer("setNextStartDate('$nextStartDate');"); ?>
    <?php endif; ?>

    <?php $this->Js->buffer("setupEventAccordion();"); ?>
<?php endif; ?>
