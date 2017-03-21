<?php
/**
  * @var \App\View\AppView $this
  */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('Edit User'), ['action' => 'edit', $user->id]) ?> </li>
        <li><?= $this->Form->postLink(__('Delete User'), ['action' => 'delete', $user->id], ['confirm' => __('Are you sure you want to delete # {0}?', $user->id)]) ?> </li>
        <li><?= $this->Html->link(__('List Users'), ['action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New User'), ['action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Event Series'), ['controller' => 'EventSeries', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Event Series'), ['controller' => 'EventSeries', 'action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Events'), ['controller' => 'Events', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Event'), ['controller' => 'Events', 'action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Images'), ['controller' => 'Images', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Image'), ['controller' => 'Images', 'action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Tags'), ['controller' => 'Tags', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Tag'), ['controller' => 'Tags', 'action' => 'add']) ?> </li>
    </ul>
</nav>
<div class="users view large-9 medium-8 columns content">
    <h3><?= h($user->name) ?></h3>
    <table class="vertical-table">
        <tr>
            <th scope="row"><?= __('Name') ?></th>
            <td><?= h($user->name) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Role') ?></th>
            <td><?= h($user->role) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Email') ?></th>
            <td><?= h($user->email) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Password') ?></th>
            <td><?= h($user->password) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Id') ?></th>
            <td><?= $this->Number->format($user->id) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Mailing List Id') ?></th>
            <td><?= $this->Number->format($user->mailing_list_id) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Facebook Id') ?></th>
            <td><?= $this->Number->format($user->facebook_id) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Created') ?></th>
            <td><?= h($user->created) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Modified') ?></th>
            <td><?= h($user->modified) ?></td>
        </tr>
    </table>
    <div class="row">
        <h4><?= __('Bio') ?></h4>
        <?= $this->Text->autoParagraph(h($user->bio)); ?>
    </div>
    <div class="related">
        <h4><?= __('Related Event Series') ?></h4>
        <?php if (!empty($user->event_series)): ?>
        <table cellpadding="0" cellspacing="0">
            <tr>
                <th scope="col"><?= __('Id') ?></th>
                <th scope="col"><?= __('Title') ?></th>
                <th scope="col"><?= __('User Id') ?></th>
                <th scope="col"><?= __('Published') ?></th>
                <th scope="col"><?= __('Created') ?></th>
                <th scope="col"><?= __('Modified') ?></th>
                <th scope="col" class="actions"><?= __('Actions') ?></th>
            </tr>
            <?php foreach ($user->event_series as $eventSeries): ?>
            <tr>
                <td><?= h($eventSeries->id) ?></td>
                <td><?= h($eventSeries->title) ?></td>
                <td><?= h($eventSeries->user_id) ?></td>
                <td><?= h($eventSeries->published) ?></td>
                <td><?= h($eventSeries->created) ?></td>
                <td><?= h($eventSeries->modified) ?></td>
                <td class="actions">
                    <?= $this->Html->link(__('View'), ['controller' => 'EventSeries', 'action' => 'view', $eventSeries->id]) ?>
                    <?= $this->Html->link(__('Edit'), ['controller' => 'EventSeries', 'action' => 'edit', $eventSeries->id]) ?>
                    <?= $this->Form->postLink(__('Delete'), ['controller' => 'EventSeries', 'action' => 'delete', $eventSeries->id], ['confirm' => __('Are you sure you want to delete # {0}?', $eventSeries->id)]) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
    </div>
    <div class="related">
        <h4><?= __('Related Events') ?></h4>
        <?php if (!empty($user->events)): ?>
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
            <?php foreach ($user->events as $events): ?>
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
        <h4><?= __('Related Images') ?></h4>
        <?php if (!empty($user->images)): ?>
        <table cellpadding="0" cellspacing="0">
            <tr>
                <th scope="col"><?= __('Id') ?></th>
                <th scope="col"><?= __('Filename') ?></th>
                <th scope="col"><?= __('Is Flyer') ?></th>
                <th scope="col"><?= __('User Id') ?></th>
                <th scope="col"><?= __('Created') ?></th>
                <th scope="col"><?= __('Modified') ?></th>
                <th scope="col" class="actions"><?= __('Actions') ?></th>
            </tr>
            <?php foreach ($user->images as $images): ?>
            <tr>
                <td><?= h($images->id) ?></td>
                <td><?= h($images->filename) ?></td>
                <td><?= h($images->is_flyer) ?></td>
                <td><?= h($images->user_id) ?></td>
                <td><?= h($images->created) ?></td>
                <td><?= h($images->modified) ?></td>
                <td class="actions">
                    <?= $this->Html->link(__('View'), ['controller' => 'Images', 'action' => 'view', $images->id]) ?>
                    <?= $this->Html->link(__('Edit'), ['controller' => 'Images', 'action' => 'edit', $images->id]) ?>
                    <?= $this->Form->postLink(__('Delete'), ['controller' => 'Images', 'action' => 'delete', $images->id], ['confirm' => __('Are you sure you want to delete # {0}?', $images->id)]) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
    </div>
    <div class="related">
        <h4><?= __('Related Tags') ?></h4>
        <?php if (!empty($user->tags)): ?>
        <table cellpadding="0" cellspacing="0">
            <tr>
                <th scope="col"><?= __('Id') ?></th>
                <th scope="col"><?= __('Parent Id') ?></th>
                <th scope="col"><?= __('Lft') ?></th>
                <th scope="col"><?= __('Rght') ?></th>
                <th scope="col"><?= __('Name') ?></th>
                <th scope="col"><?= __('Listed') ?></th>
                <th scope="col"><?= __('Selectable') ?></th>
                <th scope="col"><?= __('User Id') ?></th>
                <th scope="col"><?= __('Created') ?></th>
                <th scope="col" class="actions"><?= __('Actions') ?></th>
            </tr>
            <?php foreach ($user->tags as $tags): ?>
            <tr>
                <td><?= h($tags->id) ?></td>
                <td><?= h($tags->parent_id) ?></td>
                <td><?= h($tags->lft) ?></td>
                <td><?= h($tags->rght) ?></td>
                <td><?= h($tags->name) ?></td>
                <td><?= h($tags->listed) ?></td>
                <td><?= h($tags->selectable) ?></td>
                <td><?= h($tags->user_id) ?></td>
                <td><?= h($tags->created) ?></td>
                <td class="actions">
                    <?= $this->Html->link(__('View'), ['controller' => 'Tags', 'action' => 'view', $tags->id]) ?>
                    <?= $this->Html->link(__('Edit'), ['controller' => 'Tags', 'action' => 'edit', $tags->id]) ?>
                    <?= $this->Form->postLink(__('Delete'), ['controller' => 'Tags', 'action' => 'delete', $tags->id], ['confirm' => __('Are you sure you want to delete # {0}?', $tags->id)]) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
    </div>
</div>
