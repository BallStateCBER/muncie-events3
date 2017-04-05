<?php
    $total = $this->Paginator->counter(array('format' => '{:count}'));
?>
<h1 class="page_title">
    <?php echo $total; ?>
    <?php echo ($direction == 'future') ? 'Upcoming' : 'Past'; ?>
    Event<?php echo($total == 1 ? '' : 's'); ?>
    with Tag: <?php echo ucwords($tag['Tag']['name']); ?>
</h1>


<?php if ($count_other_direction): ?>
    <?php
        $link_text = $count_other_direction.' ';
        $link_text .= ($direction == 'future') ? 'past' : 'upcoming';
        $link_text .= ' event';
        $link_text .= $count_other_direction == 1 ? '' : 's';
        echo $this->Html->link($link_text, array(
            'controller' => 'events',
            'action' => 'tag',
            'slug' => $slug,
            'direction' => ($direction == 'future') ? 'past' : 'future'
        ));
    ?>
    with this tag
<?php else: ?>
    <p class="light_text">
        There are no <?php echo(($direction == 'future') ? 'past' : 'upcoming'); ?> events with this tag.
    </p>
<?php endif; ?>

<?php if (isset($events) && !empty($events)): ?>

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
