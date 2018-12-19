<?php
/**
 * @var \App\View\AppView $this
 */

$s = $oppCount == 1 ? '' : 's';
$z = $count == 1 ? '' : 's';

?>
<h1><?= "$count $direction event$z at $location" ?></h1>
<?= $this->Html->link(
    "$oppCount $opposite event$s",
    [
        'controller' => 'events',
        'action' => 'location', $slug, $opposite
    ],
    [
        'escape' => false
    ]
) ?>
<?= $this->element('events/accordion_wrapper') ?>
<?= $this->element('pagination') ?>
