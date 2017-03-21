<?php
/**
  * @var \App\View\AppView $this
  */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('Edit Event Series'), ['action' => 'edit', $eventSeries->id]) ?> </li>
        <li><?= $this->Form->postLink(__('Delete Event Series'), ['action' => 'delete', $eventSeries->id], ['confirm' => __('Are you sure you want to delete # {0}?', $eventSeries->id)]) ?> </li>
        <li><?= $this->Html->link(__('List Event Series'), ['action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Event Series'), ['action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Users'), ['controller' => 'Users', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New User'), ['controller' => 'Users', 'action' => 'add']) ?> </li>
    </ul>
</nav>
<div class="eventSeries view large-9 medium-8 columns content">
    <h3><?= h($eventSeries->title) ?></h3>
    <table class="vertical-table">
        <tr>
            <th scope="row"><?= __('Title') ?></th>
            <td><?= h($eventSeries->title) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('User') ?></th>
            <td><?= $eventSeries->has('user') ? $this->Html->link($eventSeries->user->name, ['controller' => 'Users', 'action' => 'view', $eventSeries->user->id]) : '' ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Id') ?></th>
            <td><?= $this->Number->format($eventSeries->id) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Created') ?></th>
            <td><?= h($eventSeries->created) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Modified') ?></th>
            <td><?= h($eventSeries->modified) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Published') ?></th>
            <td><?= $eventSeries->published ? __('Yes') : __('No'); ?></td>
        </tr>
    </table>
</div>
