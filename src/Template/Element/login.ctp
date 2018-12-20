<?php
/**
 * @var \App\View\AppView $this
 */
?>
<div id="login">
    <?= $this->Form->create('User') ?>
    <div class='form-group col-lg-4 col-xs-12'>
        <?= $this->Form->control('email', ['class' => 'form-control']) ?>
    </div>
    <div class='form-group col-lg-4 col-xs-12'>
        <?= $this->Form->control('password', ['class' => 'form-control']) ?>
        <?= $this->Html->link('Forgot password?', ['controller' => 'Users', 'action' => 'forgotPassword'], ['class' => 'nav-link float-right']) ?>
    </div>
    <div class="form-group col-lg-4 col-xs-12">
        <div class="float-right">
            <?= $this->Form->control(
                'remember_me',
                [
                    'type' => 'checkbox',
                    'label' => [
                        'text' => ' Remember me',
                        'style' => 'display: inline;'
                    ],
                    'checked' => true
                ]
            ) ?>
        </div>
    </div>
    <div class="form-group col-lg-4 col-xs-12">
        <?= $this->Form->button('Login', [
            'class' => 'btn btn-secondary float-right',
            'style' => 'margin:20px;'
        ]) ?>
        <?= $this->Form->end() ?>
    </div>
</div>

<div>
    <!--?= $this->Facebook->loginLink([
        'label' => "<img src='<!?= $fullBaseUrl ?>img/fb_login.png' />",
        'show-faces' => false,
        'perms' => 'email,user_events,create_event,rsvp_event',
        'redirect' => "/users/confirm_facebook_login"
    ]); ?-->
</div>
<div>
    Don't have an account yet?

    <?= $this->Html->link('Register', [
        'controller' => 'users',
        'action' => 'register'
    ]) ?>
</div>
