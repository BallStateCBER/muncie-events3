<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
?>

<h1 class="page_title">
	<?php echo $titleForLayout; ?>
</h1>
<p>
	Send in any questions or comments through this form and we will do our best
	to respond quickly. If you would prefer to do the emailing yourself,
	you can send a message to a site administrator at
	<a href="mailto:<?php echo Configure::read('admin_email'); ?>"><?php echo Configure::read('admin_email'); ?></a>.
</p>

<?php echo $this->Form->create('Dummy', [
	'url' => [
		'controller' => 'pages',
		'action' => 'contact'
	]
]); ?>
<?php echo $this->Form->input('category', [
	'label' => 'Category',
	'class' => 'form-control',
	'div' => [
		'class'=>'form-group col-lg-8 col-xs-12'
	],
	'options' => $categories
]); ?>
<?php echo $this->Form->input('name', [
	#'default' => $this->request->session->read('Users.name'),
	'class' => 'form-control',
	'div' => [
		'class'=>'form-group col-lg-8 col-xs-12'
	]
]); ?>
<?php echo $this->Form->input('email', [
	#'default' => $this->request->session->read('Users.email'),
	'class' => 'form-control',
	'div' => [
		'class'=>'form-group col-lg-8 col-xs-12'
	]
]); ?>
<?php echo $this->Form->input('body', [
	'label' => 'Message',
	'type' => 'textarea',
	'class' => 'form-control',
	'div' => [
		'class'=>'form-group col-lg-8 col-xs-12'
	]
]); ?>
<?php if (!$AuthUser): ?>
	<div class="g-recaptcha" data-sitekey="YOUR-SITEKEY"></div>
<?php endif; ?>
<?php echo $this->Form->submit('Send', [
	'class'=>'btn btn-secondary btn-sm',
	'div' => [
		'class'=>'form-group col-lg-8 col-xs-12'
	]
]); ?>
