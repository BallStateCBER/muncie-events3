<?php
    $logged_in = (boolean) $this->request->session()->read('Auth.User.id');
    $user_role = $this->request->session()->read('Auth.User.role');
    $this->Paginator->options([
        'url' => [
            'controller' => 'users',
            'action' => 'view',
            'id' => $user->id
        ]
    ]);
?>

<div id="user_view">

    <h1 class="page_title">
        <?= $user->name ?>
    </h1>
    <span class="email">
        <?php if ($logged_in): ?>
            <a href="mailto:<?= $user->email ?>">
                <?= $user->email ?>
            </a>
        <?php else: ?>
            <?= $this->Html->link('Log in', [
                'controller' => 'users', 'action' => 'login'
            ]); ?> to view email address.
        <?php endif; ?>
    </span>
    <p>
        <?= $user->name ?> has been a member of Muncie Events since <?= $this->Calendar->date($user->created); ?>.
    </p>
    <div class="row">
        <?php if ($user->bio): ?>
            <div class="col-lg-6">
                <h3>Bio</h3>
                <?= $user->bio ?>
            </div>
        <?php else: ?>
        <?php endif; ?>
        <?php if ($user->photo): ?>
            <div class="col-lg-6">
                <?= $this->Html->image('users/'.$user->id."/".$user->photo, ['alt' => $user->name]); ?>
            </div>
        <?php else: ?>
        <?php endif; ?>
    </div>
    <h2>
        <?= $eventCount; ?> Event<?= $eventCount == 1 ? '' : 's'; ?> Contributed:
    </h2>

    <?= $this->element('events/accordion'); ?>

    <?php $this->Js->buffer("setupEventAccordion();"); ?>

</div>
