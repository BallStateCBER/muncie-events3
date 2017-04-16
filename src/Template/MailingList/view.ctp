<?php
/**
  * @var \App\View\AppView $this
  */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('Edit Mailing List'), ['action' => 'edit', $mailingList->id]) ?> </li>
        <li><?= $this->Form->postLink(__('Delete Mailing List'), ['action' => 'delete', $mailingList->id], ['confirm' => __('Are you sure you want to delete # {0}?', $mailingList->id)]) ?> </li>
        <li><?= $this->Html->link(__('List Mailing List'), ['action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Mailing List'), ['action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Users'), ['controller' => 'Users', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New User'), ['controller' => 'Users', 'action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Categories'), ['controller' => 'Categories', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Category'), ['controller' => 'Categories', 'action' => 'add']) ?> </li>
    </ul>
</nav>
<div class="mailingList view large-9 medium-8 columns content">
    <h3><?= h($mailingList->id) ?></h3>
    <table class="vertical-table">
        <tr>
            <th scope="row"><?= __('Email') ?></th>
            <td><?= h($mailingList->email) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Categories') ?></th>
            <td><?= h($mailingList->categories) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Id') ?></th>
            <td><?= $this->Number->format($mailingList->id) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Created') ?></th>
            <td><?= h($mailingList->created) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Modified') ?></th>
            <td><?= h($mailingList->modified) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Processed Daily') ?></th>
            <td><?= h($mailingList->processed_daily) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Processed Weekly') ?></th>
            <td><?= h($mailingList->processed_weekly) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('All Categories') ?></th>
            <td><?= $mailingList->all_categories ? __('Yes') : __('No'); ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Weekly') ?></th>
            <td><?= $mailingList->weekly ? __('Yes') : __('No'); ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Daily Sun') ?></th>
            <td><?= $mailingList->daily_sun ? __('Yes') : __('No'); ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Daily Mon') ?></th>
            <td><?= $mailingList->daily_mon ? __('Yes') : __('No'); ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Daily Tue') ?></th>
            <td><?= $mailingList->daily_tue ? __('Yes') : __('No'); ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Daily Wed') ?></th>
            <td><?= $mailingList->daily_wed ? __('Yes') : __('No'); ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Daily Thu') ?></th>
            <td><?= $mailingList->daily_thu ? __('Yes') : __('No'); ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Daily Fri') ?></th>
            <td><?= $mailingList->daily_fri ? __('Yes') : __('No'); ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Daily Sat') ?></th>
            <td><?= $mailingList->daily_sat ? __('Yes') : __('No'); ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('New Subscriber') ?></th>
            <td><?= $mailingList->new_subscriber ? __('Yes') : __('No'); ?></td>
        </tr>
    </table>
    <div class="related">
        <h4><?= __('Related Users') ?></h4>
        <?php if (!empty($mailingList->users)): ?>
        <table cellpadding="0" cellspacing="0">
            <tr>
                <th scope="col"><?= __('Id') ?></th>
                <th scope="col"><?= __('Name') ?></th>
                <th scope="col"><?= __('Role') ?></th>
                <th scope="col"><?= __('Bio') ?></th>
                <th scope="col"><?= __('Email') ?></th>
                <th scope="col"><?= __('Password') ?></th>
                <th scope="col"><?= __('Mailing List Id') ?></th>
                <th scope="col"><?= __('Facebook Id') ?></th>
                <th scope="col"><?= __('Created') ?></th>
                <th scope="col"><?= __('Modified') ?></th>
                <th scope="col" class="actions"><?= __('Actions') ?></th>
            </tr>
            <?php foreach ($mailingList->users as $users): ?>
            <tr>
                <td><?= h($users->id) ?></td>
                <td><?= h($users->name) ?></td>
                <td><?= h($users->role) ?></td>
                <td><?= h($users->bio) ?></td>
                <td><?= h($users->email) ?></td>
                <td><?= h($users->password) ?></td>
                <td><?= h($users->mailing_list_id) ?></td>
                <td><?= h($users->facebook_id) ?></td>
                <td><?= h($users->created) ?></td>
                <td><?= h($users->modified) ?></td>
                <td class="actions">
                    <?= $this->Html->link(__('View'), ['controller' => 'Users', 'action' => 'view', $users->id]) ?>
                    <?= $this->Html->link(__('Edit'), ['controller' => 'Users', 'action' => 'edit', $users->id]) ?>
                    <?= $this->Form->postLink(__('Delete'), ['controller' => 'Users', 'action' => 'delete', $users->id], ['confirm' => __('Are you sure you want to delete # {0}?', $users->id)]) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
    </div>
    <div class="related">
        <h4><?= __('Related Categories') ?></h4>
        <?php if (!empty($mailingList->categories)): ?>
        <table cellpadding="0" cellspacing="0">
            <tr>
                <th scope="col"><?= __('Id') ?></th>
                <th scope="col"><?= __('Name') ?></th>
                <th scope="col"><?= __('Slug') ?></th>
                <th scope="col"><?= __('Weight') ?></th>
                <th scope="col" class="actions"><?= __('Actions') ?></th>
            </tr>
            <?php foreach ($mailingList->categories as $categories): ?>
            <tr>
                <td><?= h($categories->id) ?></td>
                <td><?= h($categories->name) ?></td>
                <td><?= h($categories->slug) ?></td>
                <td><?= h($categories->weight) ?></td>
                <td class="actions">
                    <?= $this->Html->link(__('View'), ['controller' => 'Categories', 'action' => 'view', $categories->id]) ?>
                    <?= $this->Html->link(__('Edit'), ['controller' => 'Categories', 'action' => 'edit', $categories->id]) ?>
                    <?= $this->Form->postLink(__('Delete'), ['controller' => 'Categories', 'action' => 'delete', $categories->id], ['confirm' => __('Are you sure you want to delete # {0}?', $categories->id)]) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
    </div>
</div>
