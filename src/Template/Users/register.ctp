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
    <?= $this->Recaptcha->display() ?>
    <?= $this->Form->end() ?>
</div>
