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
                ['action' => 'delete', $mailingList->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $mailingList->id)]
            )
        ?></li>
        <li><?= $this->Html->link(__('List Mailing List'), ['action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('List Users'), ['controller' => 'Users', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New User'), ['controller' => 'Users', 'action' => 'add']) ?></li>
        <li><?= $this->Html->link(__('List Categories'), ['controller' => 'Categories', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New Category'), ['controller' => 'Categories', 'action' => 'add']) ?></li>
    </ul>
</nav>
<div class="mailingList form large-9 medium-8 columns content">
    <?= $this->Form->create($mailingList) ?>
    <fieldset>
        <legend><?= __('Edit Mailing List') ?></legend>
        <?php
            echo $this->Form->control('email');
            echo $this->Form->control('all_categories');
            echo $this->Form->control('categories');
            echo $this->Form->control('weekly');
            echo $this->Form->control('daily_sun');
            echo $this->Form->control('daily_mon');
            echo $this->Form->control('daily_tue');
            echo $this->Form->control('daily_wed');
            echo $this->Form->control('daily_thu');
            echo $this->Form->control('daily_fri');
            echo $this->Form->control('daily_sat');
            echo $this->Form->control('new_subscriber');
            echo $this->Form->control('processed_daily', ['empty' => true]);
            echo $this->Form->control('processed_weekly', ['empty' => true]);
            echo $this->Form->control('categories._ids', ['options' => $categories]);
        ?>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
