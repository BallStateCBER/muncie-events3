<?php
/**
  * @var \App\View\AppView $this
  */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('New Mailing List'), ['action' => 'add']) ?></li>
        <li><?= $this->Html->link(__('List Users'), ['controller' => 'Users', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New User'), ['controller' => 'Users', 'action' => 'add']) ?></li>
        <li><?= $this->Html->link(__('List Categories'), ['controller' => 'Categories', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New Category'), ['controller' => 'Categories', 'action' => 'add']) ?></li>
    </ul>
</nav>
<div class="mailingList index large-9 medium-8 columns content">
    <h3><?= __('Mailing List') ?></h3>
    <table cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th scope="col"><?= $this->Paginator->sort('id') ?></th>
                <th scope="col"><?= $this->Paginator->sort('email') ?></th>
                <th scope="col"><?= $this->Paginator->sort('all_categories') ?></th>
                <th scope="col"><?= $this->Paginator->sort('categories') ?></th>
                <th scope="col"><?= $this->Paginator->sort('weekly') ?></th>
                <th scope="col"><?= $this->Paginator->sort('daily_sun') ?></th>
                <th scope="col"><?= $this->Paginator->sort('daily_mon') ?></th>
                <th scope="col"><?= $this->Paginator->sort('daily_tue') ?></th>
                <th scope="col"><?= $this->Paginator->sort('daily_wed') ?></th>
                <th scope="col"><?= $this->Paginator->sort('daily_thu') ?></th>
                <th scope="col"><?= $this->Paginator->sort('daily_fri') ?></th>
                <th scope="col"><?= $this->Paginator->sort('daily_sat') ?></th>
                <th scope="col"><?= $this->Paginator->sort('new_subscriber') ?></th>
                <th scope="col"><?= $this->Paginator->sort('created') ?></th>
                <th scope="col"><?= $this->Paginator->sort('modified') ?></th>
                <th scope="col"><?= $this->Paginator->sort('processed_daily') ?></th>
                <th scope="col"><?= $this->Paginator->sort('processed_weekly') ?></th>
                <th scope="col" class="actions"><?= __('Actions') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($mailingList as $mailingList): ?>
            <tr>
                <td><?= $this->Number->format($mailingList->id) ?></td>
                <td><?= h($mailingList->email) ?></td>
                <td><?= h($mailingList->all_categories) ?></td>
                <td><?= h($mailingList->categories) ?></td>
                <td><?= h($mailingList->weekly) ?></td>
                <td><?= h($mailingList->daily_sun) ?></td>
                <td><?= h($mailingList->daily_mon) ?></td>
                <td><?= h($mailingList->daily_tue) ?></td>
                <td><?= h($mailingList->daily_wed) ?></td>
                <td><?= h($mailingList->daily_thu) ?></td>
                <td><?= h($mailingList->daily_fri) ?></td>
                <td><?= h($mailingList->daily_sat) ?></td>
                <td><?= h($mailingList->new_subscriber) ?></td>
                <td><?= h($mailingList->created) ?></td>
                <td><?= h($mailingList->modified) ?></td>
                <td><?= h($mailingList->processed_daily) ?></td>
                <td><?= h($mailingList->processed_weekly) ?></td>
                <td class="actions">
                    <?= $this->Html->link(__('View'), ['action' => 'view', $mailingList->id]) ?>
                    <?= $this->Html->link(__('Edit'), ['action' => 'edit', $mailingList->id]) ?>
                    <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $mailingList->id], ['confirm' => __('Are you sure you want to delete # {0}?', $mailingList->id)]) ?>
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
