<?php
/**
  * @var \App\View\AppView $this
  */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('List Categories'), ['action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('List Events'), ['controller' => 'Events', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New Event'), ['controller' => 'Events', 'action' => 'add']) ?></li>
        <li><?= $this->Html->link(__('List Mailing List'), ['controller' => 'MailingList', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New Mailing List'), ['controller' => 'MailingList', 'action' => 'add']) ?></li>
    </ul>
</nav>
<div class="categories form large-9 medium-8 columns content">
    <?= $this->Form->create($category) ?>
    <fieldset>
        <legend><?= __('Add Category') ?></legend>
        <?php
            echo $this->Form->control('name');
            echo $this->Form->control('slug');
            echo $this->Form->control('weight');
            echo $this->Form->control('mailing_list._ids', ['options' => $mailingList]);
        ?>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
