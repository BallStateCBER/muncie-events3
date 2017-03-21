<?php
/**
  * @var \App\View\AppView $this
  */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $eventSeries->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $eventSeries->id)]
            )
        ?></li>
        <li><?= $this->Html->link(__('List Event Series'), ['action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('List Users'), ['controller' => 'Users', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New User'), ['controller' => 'Users', 'action' => 'add']) ?></li>
    </ul>
</nav>
<div class="eventSeries form large-9 medium-8 columns content">
    <?= $this->Form->create($eventSeries) ?>
    <fieldset>
        <legend><?= __('Edit Event Series') ?></legend>
        <?php
            echo $this->Form->control('title');
            echo $this->Form->control('user_id', ['options' => $users, 'empty' => true]);
            echo $this->Form->control('published');
        ?>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
