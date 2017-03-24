<div id="login">
	<?= $this->Form->create('User', ['url' => ['controller' => 'Users', 'action' => 'login']]); ?>
	<div class='form-group col-lg-4 col-xs-12'>
		<?= $this->Form->control('email', ['class' => 'form-control']); ?>
	</div>
	<div class='form-group col-lg-4 col-xs-12'>
		<?= $this->Form->control('password', ['class' => 'form-control']); ?>
		<?= $this->Html->link(__('Forgot password?'), ['controller' => 'Users', 'action' => 'forgotPassword'], ['class' => 'nav-link']); ?>
	</div>
	<?= $this->Form->input('remember_me', [
			'type' => 'checkbox',
			'label' => [
				'text' => ' Remember me',
				'style' => 'display: inline;'
			],
			'checked' => true,
			'div' => [
				'class'=>'form-group col-lg-4 col-xs-12'
			]
		]);
	?>
	<?= $this->Form->button(__('Login'), ['class' => 'btn btn-secondary btn-sm']); ?>
	<?= $this->Form->end() ?>
	Or log in with Facebook: <?php /*echo $this->Facebook->login(array(
		'label' => 'Log in with Facebook',
		'img' => 'fb_login.png',
		'show-faces' => false,
		'perms' => 'email,user_events,create_event,rsvp_event',
		'redirect' => "/users/confirm_facebook_login?redirect=$redirect"
	)); */ ?>
</div>

Don't have an account yet?

<?php echo $this->Html->link(
	'Register',
	array(
		'controller' => 'users',
		'action' => 'register'
	)
); ?>
