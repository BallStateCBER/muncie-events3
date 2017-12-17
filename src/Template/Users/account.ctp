<div class='form-group'>
    <?= $this->Form->create($user, [
        'type' => 'file'
        ]);
    ?>
    <h1 class="page_title">
        <?= $titleForLayout; ?>
    </h1>

    <div class="col-lg-6 col-md-12 userPhoto">
    <?php if ($user->photo) {
        echo $this->Html->image('users' . DS . $user->photo, [
            'alt' => $user->name
        ]);
    } ?>
    </div>
    <div class="col-lg-6">
        <?= $this->Form->control('name', ['class' => 'form-control', 'default' => $this->request->session()->read('Auth.User.name')]); ?>
    </div>
    <div class="col-lg-6">
        <?= $this->Form->input('photo', [
            'type' => 'file',
            'label' => 'User photo'
        ]); ?>
    </div>
    <div class="col-lg-6">
        <?= $this->Form->control('email', ['class' => 'form-control', 'default' => $this->request->session()->read('Auth.User.email')]); ?>
    </div>
    <div class="col-lg-12">
        <?= $this->Form->control('bio', [
            'class' => 'form-control',
            'default' => $this->request->session()->read('Auth.User.bio'),
            'type' => 'textarea'
        ]); ?>
        <?= $this->Html->link(__('Do you want to change your password?'), $reset_url, ['class' => 'nav-link']); ?>
    </div>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
