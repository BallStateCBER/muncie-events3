<?php
    use Cake\Utility\Inflector;

$s = $oppCount == 1 ? '' : 's';

?>
<h1><?= "$count $direction events at $location" ?></h1>
<?= $this->Html->link(
    "$oppCount $opposite event$s",
    [
        'controller' => 'events',
        'action' => 'location', $location, $opposite
    ],
    [
        'escape' => false
    ]
) ?>
<?= $this->element('events/accordion_wrapper') ?>
<?= $this->element('pagination') ?>
