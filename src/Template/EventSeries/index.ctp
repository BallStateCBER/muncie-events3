<?php
/**
  * @var \App\View\AppView $this
  */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('New Event Series'), ['action' => 'add']) ?></li>
        <li><?= $this->Html->link(__('List Users'), ['controller' => 'Users', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New User'), ['controller' => 'Users', 'action' => 'add']) ?></li>
    </ul>
</nav>
<div class="eventSeries index large-9 medium-8 columns content">
    <h3><?= __('Event Series') ?></h3>
    <table cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th scope="col"><?= $this->Paginator->sort('id') ?></th>
                <th scope="col"><?= $this->Paginator->sort('title') ?></th>
                <th scope="col"><?= $this->Paginator->sort('user_id') ?></th>
                <th scope="col"><?= $this->Paginator->sort('published') ?></th>
                <th scope="col"><?= $this->Paginator->sort('created') ?></th>
                <th scope="col"><?= $this->Paginator->sort('modified') ?></th>
                <th scope="col" class="actions"><?= __('Actions') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($eventSeries as $eventSeries): ?>
            <tr>
                <td><?= $this->Number->format($eventSeries->id) ?></td>
                <td><?= h($eventSeries->title) ?></td>
                <td><?= $eventSeries->has('user') ? $this->Html->link($eventSeries->user->name, ['controller' => 'Users', 'action' => 'view', $eventSeries->user->id]) : '' ?></td>
                <td><?= h($eventSeries->published) ?></td>
                <td><?= h($eventSeries->created) ?></td>
                <td><?= h($eventSeries->modified) ?></td>
                <td class="actions">
                    <?= $this->Html->link(__('View'), ['action' => 'view', $eventSeries->id]) ?>
                    <?= $this->Html->link(__('Edit'), ['action' => 'edit', $eventSeries->id]) ?>
                    <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $eventSeries->id], ['confirm' => __('Are you sure you want to delete # {0}?', $eventSeries->id)]) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div class="paginator">
        <ul class="pagination">
            <?= $this->Paginator->first('<< ' . __('first')) ?>
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
            <?= $this->Paginator->last(__('last') . ' >>') ?>
        </ul>
        <p><?= $this->Paginator->counter(['format' => __('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')]) ?></p>
    </div>
</div>
