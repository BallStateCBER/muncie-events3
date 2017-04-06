<h1 class="page_title">
    <?php echo $titleForLayout; ?>
</h1>

<div class="prev_next_day">
    <?php echo $this->Calendar->prevDay(mktime(0, 0, 0, $month, $day - 1, $year)); ?>
    <?php echo $this->Calendar->nextDay(mktime(0, 0, 0, $month, $day + 1, $year)); ?>
</div>
<?php echo $this->element('events/date_picker', ['default' => "$month/$day/$year"]); ?>

<?php if (empty($events)): ?>
    <p class="alert alert-info">
        Sorry, but no events
        <?php if ("$month$day$year" >= date('mdY')): ?>
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
            'd' => $day,
            'y' => $year
        ]); ?>.
    </p>
<?php else: ?>
    <?php echo $this->element('events/accordion', ['open_only_event' => true]); ?>
<?php endif; ?>
