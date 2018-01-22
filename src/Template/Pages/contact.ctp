<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;

?>

<h1 class="page_title">
    <?= $titleForLayout; ?>
</h1>
<p>
    Send in any questions or comments through this form and we will do our best
    to respond quickly. If you would prefer to do the emailing yourself,
    you can send a message to a site administrator at
    <a href="mailto:<?= Configure::read('admin_email'); ?>"><?= Configure::read('admin_email'); ?></a>.
</p>

<?= $this->Form->create('Dummy', [
    'url' => [
        'controller' => 'pages',
        'action' => 'contact'
    ]
]); ?>
<div class="form-group col-lg-8 col-xs-12">
    <?= $this->Form->input('category', [
        'label' => 'Category',
        'class' => 'form-control',
        'options' => [
            'General' => 'General',
            'Website errors' => 'Website errors'
        ]
    ]); ?>
</div>
<div class='form-group col-lg-8 col-xs-12'>
    <?= $this->Form->input('name', [
        'default' => $this->request->session()->read('Auth.User.name'),
        'class' => 'form-control'
    ]); ?>
</div>
<div class='form-group col-lg-8 col-xs-12'>
    <?= $this->Form->input('email', [
        'default' => $this->request->session()->read('Auth.User.email'),
        'class' => 'form-control'
    ]); ?>
</div>
<div class='form-group col-lg-8 col-xs-12'>
    <?= $this->Form->input('body', [
        'label' => 'Message',
        'type' => 'textarea',
        'class' => 'form-control'
    ]); ?>
</div>
<?php if (!$this->request->session()->read('Auth.User')): ?>
    <?= $this->Recaptcha->display() ?>
<?php endif; ?>
<div class='form-group col-lg-8 col-xs-12'>
<?= $this->Form->submit('Send', [
    'class' => 'btn btn-secondary g-recaptcha',
    'data-sitekey' => '6LfA-0EUAAAAAJSFEzAbHW0JMpujjgNzhO0ibfF-',
    'data-callback' => 'YourOnSubmitFn'
]); ?>
</div>
