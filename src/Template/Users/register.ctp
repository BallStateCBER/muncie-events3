<div class='form-group'>
    <?= $this->Form->create($user, [
        'type' => 'file'
    ]); ?>
    <h1 class="page_title">
        <?= $titleForLayout; ?>
    </h1>
    <div class="col-lg-6">
        <?= $this->Form->control('name', ['class' => 'form-control']); ?>
    </div>
    <div class="col-lg-6">
        <?= $this->Form->control('password', ['class' => 'form-control']); ?>
    </div>
    <div class="col-lg-6">
        <?= $this->Form->control('confirm_password', [
            'class' => 'form-control',
            'type' => 'password'
        ]); ?>
    </div>
    <div class="col-lg-6">
        <?= $this->Form->control('email', ['class' => 'form-control']); ?>
    </div>
    <div class="g-recaptcha" data-sitekey="6Lcg6tkSAAAAALkenFi1dIQ5B-4BVLJur5hYl-2J"></div>
    <?= $this->Form->submit('Send', [
        'class' => 'btn btn-secondary g-recaptcha',
        'data-sitekey' => '6LfA-0EUAAAAAJSFEzAbHW0JMpujjgNzhO0ibfF-',
        'data-callback' => 'YourOnSubmitFn'
    ]); ?>
    <?= $this->Form->end() ?>
</div>
