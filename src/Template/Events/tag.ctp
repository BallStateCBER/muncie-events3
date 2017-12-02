<?php
    use Cake\Utility\Inflector;

$s = $oppCount == 1 ? '' : 's';
$z = $count == 1 ? '' : 's';

?>

<h1 class="page_title">
    <?= "$count $direction Event$z with $titleForLayout" ?>
</h1>

<?= $this->Html->link(
        "$oppCount $opposite event$s",
        [
            'controller' => 'events',
            'action' => 'tag',
            'slug' => $tag['id'].'_'.Inflector::slug($tag['name']),
            'direction' => $opposite
        ]
    );
?>

<?php if (isset($events) && !empty($events)): ?>

    <?= $this->element('events/accordion_wrapper'); ?>

    <?php $this->Js->buffer("setupEventAccordion();"); ?>

    <?= $this->element('pagination') ?>

<?php else: ?>
    <p class="alert alert-info">
        No events found.
    </p>
<?php endif; ?>
