<?php
    use Cake\Utility\Inflector;

?>
<h1><?= $location ?></h1>
<?= $this->Html->link(
    "Click for $opposite events",
    [
        'controller' => 'events',
        'action' => 'location',
        'location' => $location,
        'direction' => $opposite
    ],
    [
        'escape' => false
    ]
) ?>
<?= $this->element('events/accordion_wrapper') ?>
<?= $this->element('pagination') ?>
