<?php
/**
  * @var \App\View\AppView $this
  */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('Edit Category'), ['action' => 'edit', $category->id]) ?> </li>
        <li><?= $this->Form->postLink(__('Delete Category'), ['action' => 'delete', $category->id], ['confirm' => __('Are you sure you want to delete # {0}?', $category->id)]) ?> </li>
        <li><?= $this->Html->link(__('List Categories'), ['action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Category'), ['action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Events'), ['controller' => 'Events', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Event'), ['controller' => 'Events', 'action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Mailing List'), ['controller' => 'MailingList', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Mailing List'), ['controller' => 'MailingList', 'action' => 'add']) ?> </li>
    </ul>
</nav>
<div class="categories view large-9 medium-8 columns content">
    <h3><?= h($category->name) ?></h3>
    <table class="vertical-table">
        <tr>
            <th scope="row"><?= __('Name') ?></th>
            <td><?= h($category->name) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Slug') ?></th>
            <td><?= h($category->slug) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Id') ?></th>
            <td><?= $this->Number->format($category->id) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Weight') ?></th>
            <td><?= $this->Number->format($category->weight) ?></td>
        </tr>
    </table>
    <div class="related">
        <h4><?= __('Related Events') ?></h4>
        <?php if (!empty($category->events)): ?>
        <table cellpadding="0" cellspacing="0">
            <tr>
                <th scope="col"><?= __('Id') ?></th>
                <th scope="col"><?= __('Title') ?></th>
                <th scope="col"><?= __('Description') ?></th>
                <th scope="col"><?= __('Location') ?></th>
                <th scope="col"><?= __('Location Details') ?></th>
                <th scope="col"><?= __('Address') ?></th>
                <th scope="col"><?= __('User Id') ?></th>
                <th scope="col"><?= __('Category Id') ?></th>
                <th scope="col"><?= __('Series Id') ?></th>
                <th scope="col"><?= __('Date') ?></th>
                <th scope="col"><?= __('Time Start') ?></th>
                <th scope="col"><?= __('Time End') ?></th>
                <th scope="col"><?= __('Age Restriction') ?></th>
                <th scope="col"><?= __('Cost') ?></th>
                <th scope="col"><?= __('Source') ?></th>
                <th scope="col"><?= __('Published') ?></th>
                <th scope="col"><?= __('Approved By') ?></th>
                <th scope="col"><?= __('Created') ?></th>
                <th scope="col"><?= __('Modified') ?></th>
                <th scope="col" class="actions"><?= __('Actions') ?></th>
            </tr>
            <?php foreach ($category->events as $events): ?>
            <tr>
                <td><?= h($events->id) ?></td>
                <td><?= h($events->title) ?></td>
                <td><?= h($events->description) ?></td>
                <td><?= h($events->location) ?></td>
                <td><?= h($events->location_details) ?></td>
                <td><?= h($events->address) ?></td>
                <td><?= h($events->user_id) ?></td>
                <td><?= h($events->category_id) ?></td>
                <td><?= h($events->series_id) ?></td>
                <td><?= h($events->date) ?></td>
                <td><?= h($events->time_start) ?></td>
                <td><?= h($events->time_end) ?></td>
                <td><?= h($events->age_restriction) ?></td>
                <td><?= h($events->cost) ?></td>
                <td><?= h($events->source) ?></td>
                <td><?= h($events->published) ?></td>
                <td><?= h($events->approved_by) ?></td>
                <td><?= h($events->created) ?></td>
                <td><?= h($events->modified) ?></td>
                <td class="actions">
                    <?= $this->Html->link(__('View'), ['controller' => 'Events', 'action' => 'view', $events->id]) ?>
                    <?= $this->Html->link(__('Edit'), ['controller' => 'Events', 'action' => 'edit', $events->id]) ?>
                    <?= $this->Form->postLink(__('Delete'), ['controller' => 'Events', 'action' => 'delete', $events->id], ['confirm' => __('Are you sure you want to delete # {0}?', $events->id)]) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
    </div>
    <div class="related">
        <h4><?= __('Related Mailing List') ?></h4>
        <?php if (!empty($category->mailing_list)): ?>
        <table cellpadding="0" cellspacing="0">
            <tr>
                <th scope="col"><?= __('Id') ?></th>
                <th scope="col"><?= __('Email') ?></th>
                <th scope="col"><?= __('All Categories') ?></th>
                <th scope="col"><?= __('Categories') ?></th>
                <th scope="col"><?= __('Weekly') ?></th>
                <th scope="col"><?= __('Daily Sun') ?></th>
                <th scope="col"><?= __('Daily Mon') ?></th>
                <th scope="col"><?= __('Daily Tue') ?></th>
                <th scope="col"><?= __('Daily Wed') ?></th>
                <th scope="col"><?= __('Daily Thu') ?></th>
                <th scope="col"><?= __('Daily Fri') ?></th>
                <th scope="col"><?= __('Daily Sat') ?></th>
                <th scope="col"><?= __('New Subscriber') ?></th>
                <th scope="col"><?= __('Created') ?></th>
                <th scope="col"><?= __('Modified') ?></th>
                <th scope="col"><?= __('Processed Daily') ?></th>
                <th scope="col"><?= __('Processed Weekly') ?></th>
                <th scope="col" class="actions"><?= __('Actions') ?></th>
            </tr>
            <?php foreach ($category->mailing_list as $mailingList): ?>
            <tr>
                <td><?= h($mailingList->id) ?></td>
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
                    <?= $this->Html->link(__('View'), ['controller' => 'MailingList', 'action' => 'view', $mailingList->id]) ?>
                    <?= $this->Html->link(__('Edit'), ['controller' => 'MailingList', 'action' => 'edit', $mailingList->id]) ?>
                    <?= $this->Form->postLink(__('Delete'), ['controller' => 'MailingList', 'action' => 'delete', $mailingList->id], ['confirm' => __('Are you sure you want to delete # {0}?', $mailingList->id)]) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
    </div>
</div>
