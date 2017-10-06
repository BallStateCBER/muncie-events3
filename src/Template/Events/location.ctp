<?php
    use Cake\Utility\Inflector;

$s = $oppCount == 1 ? '' : 's';

?>
<h1><?= $location ?></h1>
<?= $this->Html->link(
    "$oppCount $opposite event$s",
    [
        'controller' => 'events',
        'action' => 'location', $opposite,
        'direction' => $location,
    ],
    [
        'escape' => false
    ]
) ?>
<?= $this->element('events/accordion_wrapper') ?>
<?= $this->element('pagination') ?>
