<?php
/**
 * @var \App\View\AppView $this
 */
?>
<h1 class="page_title">
    <?= $titleForLayout; ?>
</h1>

<div class="content_box col-lg-6">
    <?= $this->Form->create('User', [
        'url' => [
            'controller' => 'Users',
            'action' => 'resetPassword',
            $user_id,
            $reset_password_hash
        ]
    ]) ?>

    <?= $this->Form->control('new_password', [
        'class' => 'form-control',
        'label' => 'New Password',
        'type' => 'password',
        'autocomplete' => 'off'
    ]) ?>

    <?= $this->Form->control('new_confirm_password', [
        'class' => 'form-control',
        'label' => 'Confirm Password',
        'type' => 'password',
        'autocomplete' => 'off'
    ]) ?>
    <?= $this->Form->submit(__('Reset Password'), ['class' => 'btn']) ?>
    <?= $this->Form->end() ?>
</div>
