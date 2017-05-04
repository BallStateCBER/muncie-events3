<h1 class="page_title">
    <?php echo $titleForLayout; ?>
</h1>

<div class="prev_next_day">
    <?php echo $this->Calendar->prevMonth($month, $year); ?>
    <?php echo $this->Calendar->nextMonth($month, $year); ?>
</div>

<?php if (empty($events)): ?>
    <p class="alert alert-info">
        Sorry, but no events
        <?php if ("$month$year" >= date('mY')): ?>
            have been
        <?php else: ?>
            were
        <?php endif; ?>
        posted for this date.
        <br />
        If you know of an event happening on this date,
        <?php echo $this->Html->link('tell us about it', [
            'controller' => 'events',
            'action' => 'add',
            'm' => $month,
            'y' => $year
        ]); ?>.
    </p>
<?php else: ?>
    <?php echo $this->element('events/accordion', ['open_only_event' => true]); ?>
<?php endif; ?>
