<?php
    use Cake\Utility\Inflector;

?>

<h1 class="page_title">
    <?= $titleForLayout ?>
</h1>

<?= $this->Html->link(
        "Click for $opposite events",
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
