<?php
/**
  * @var \App\View\AppView $this
  */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('Edit Tag'), ['action' => 'edit', $tag->id]) ?> </li>
        <li><?= $this->Form->postLink(__('Delete Tag'), ['action' => 'delete', $tag->id], ['confirm' => __('Are you sure you want to delete # {0}?', $tag->id)]) ?> </li>
        <li><?= $this->Html->link(__('List Tags'), ['action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Tag'), ['action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Parent Tags'), ['controller' => 'Tags', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Parent Tag'), ['controller' => 'Tags', 'action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Users'), ['controller' => 'Users', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New User'), ['controller' => 'Users', 'action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Events'), ['controller' => 'Events', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Event'), ['controller' => 'Events', 'action' => 'add']) ?> </li>
    </ul>
</nav>
<div class="tags view large-9 medium-8 columns content">
    <h3><?= h($tag->name) ?></h3>
    <table class="vertical-table">
        <tr>
            <th scope="row"><?= __('Parent Tag') ?></th>
            <td><?= $tag->has('parent_tag') ? $this->Html->link($tag->parent_tag->name, ['controller' => 'Tags', 'action' => 'view', $tag->parent_tag->id]) : '' ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Name') ?></th>
            <td><?= h($tag->name) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('User') ?></th>
            <td><?= $tag->has('user') ? $this->Html->link($tag->user->name, ['controller' => 'Users', 'action' => 'view', $tag->user->id]) : '' ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Id') ?></th>
            <td><?= $this->Number->format($tag->id) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Lft') ?></th>
            <td><?= $this->Number->format($tag->lft) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Rght') ?></th>
            <td><?= $this->Number->format($tag->rght) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Created') ?></th>
            <td><?= h($tag->created) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Listed') ?></th>
            <td><?= $tag->listed ? __('Yes') : __('No'); ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Selectable') ?></th>
            <td><?= $tag->selectable ? __('Yes') : __('No'); ?></td>
        </tr>
    </table>
    <div class="related">
        <h4><?= __('Related Tags') ?></h4>
        <?php if (!empty($tag->child_tags)): ?>
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
            <?php foreach ($tag->child_tags as $childTags): ?>
            <tr>
                <td><?= h($childTags->id) ?></td>
                <td><?= h($childTags->parent_id) ?></td>
                <td><?= h($childTags->lft) ?></td>
                <td><?= h($childTags->rght) ?></td>
                <td><?= h($childTags->name) ?></td>
                <td><?= h($childTags->listed) ?></td>
                <td><?= h($childTags->selectable) ?></td>
                <td><?= h($childTags->user_id) ?></td>
                <td><?= h($childTags->created) ?></td>
                <td class="actions">
                    <?= $this->Html->link(__('View'), ['controller' => 'Tags', 'action' => 'view', $childTags->id]) ?>
                    <?= $this->Html->link(__('Edit'), ['controller' => 'Tags', 'action' => 'edit', $childTags->id]) ?>
                    <?= $this->Form->postLink(__('Delete'), ['controller' => 'Tags', 'action' => 'delete', $childTags->id], ['confirm' => __('Are you sure you want to delete # {0}?', $childTags->id)]) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
    </div>
    <div class="related">
        <h4><?= __('Related Events') ?></h4>
        <?php if (!empty($tag->events)): ?>
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
            <?php foreach ($tag->events as $events): ?>
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
</div>
