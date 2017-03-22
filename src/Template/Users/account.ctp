<div class='form-group'>
	<?= $this->Form->create('User', ['url' => ['controller' => 'Users', 'action' => 'account']]); ?>
	<h1 class="page_title">
		<?php echo $titleForLayout; ?>
	</h1>
	<div class="col-lg-6">
		<?= $this->Form->control('name', ['class' => 'form-control', 'default' => $this->request->session()->read('Auth.User.name')]); ?>
	</div>
	<div class="col-lg-6">
		<?= $this->Form->control('email', ['class' => 'form-control', 'default' => $this->request->session()->read('Auth.User.email')]); ?>
	</div>
	<div class="col-lg-9">
		<?= $this->Form->control('bio', [
			'class' => 'form-control',
			'default' => $this->request->session()->read('Auth.User.bio'),
			'type' => 'textarea'
		]); ?>
	</div>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
