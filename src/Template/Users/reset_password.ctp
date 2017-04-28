<div class="content_box col-lg-6">
    <?= $this->Form->create('User', ['url' => [
            'controller' => 'users',
            'action' => 'reset_password',
            $user_id,
            $reset_password_hash
        ]]);
    ?>

    <?= $this->Form->control('new_password', [
            'class' => 'form-control',
            'label' => 'New Password',
            'type' => 'password',
            'autocomplete' => 'off'
        ]);
    ?>
    <?= $this->Form->control('new_confirm_password', [
            'class' => 'form-control',
            'label' => 'Confirm Password',
            'type' => 'password',
            'autocomplete' => 'off'
        ]);
    ?>
    <?= $this->Form->submit('Reset Password'); ?>
    <?= $this->Form->end(); ?>
</div>
