<div id="login">
    <?= $this->Form->create('User', ['url' => ['controller' => 'Users', 'action' => 'login']]); ?>
    <div class='form-group col-lg-4 col-xs-12'>
        <?= $this->Form->control('email', ['class' => 'form-control']); ?>
    </div>
    <div class='form-group col-lg-4 col-xs-12'>
        <?= $this->Form->control('password', ['class' => 'form-control']); ?>
        <?= $this->Html->link(__('Forgot password?'), ['controller' => 'Users', 'action' => 'forgotPassword'], ['class' => 'nav-link float-right']); ?>
    </div>
    <div class="form-group col-lg-4 col-xs-12">
        <div class="float-right">
            <?= $this->Form->input('remember_me', [
                    'type' => 'checkbox',
                    'label' => [
                        'text' => ' Remember me',
                        'style' => 'display: inline;'
                    ],
                    'checked' => true
                ]);
            ?>
        </div>
    </div>
    <div class="form-group col-lg-4 col-xs-12">
        <?= $this->Form->button(__('Login'), [
            'class' => 'btn btn-secondary float-right',
            'style' => 'margin:20px;'
        ]); ?>
        <?= $this->Form->end() ?>
    </div>
</div>

<div class="form-group col-lg-4 col-xs-12">
    <?= $this->Facebook->loginLink([
        'label' => "<img src='img/fb_login.png' />",
        'show-faces' => false,
        'perms' => 'email,user_events,create_event,rsvp_event',
        'redirect' => "/users/confirm_facebook_login"
    ]); ?>
</div>
<div class="form-group col-lg-4 col-xs-12">
    Don't have an account yet?

    <?= $this->Html->link('Register', [
        'controller' => 'users',
        'action' => 'register'
    ]); ?>
</div>
