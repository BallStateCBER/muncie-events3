<?php
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
        <?php if ($loggedIn): ?>
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
        <?php if ($user->bio && $user->photo): ?>
            <div class="col-lg-6">
                <h3>Bio</h3>
                <?= $user->bio ?>
            </div>
            <div class="col-lg-6 userPhoto">
                <?= $this->Html->image('users/' . $user->photo, [
                    'alt' => $user->name
                ]); ?>
            </div>
        <?php elseif (!$user->bio && $user->photo): ?>
            <div class="col-lg-12 userPhoto">
                <?= $this->Html->image('users/' . $user->photo, [
                    'alt' => $user->name
                ]); ?>
            </div>
        <?php elseif ($user->bio && !$user->photo): ?>
            <div class="col-lg-12">
                <h3>Bio</h3>
                <?= $user->bio ?>
            </div>
        <?php endif; ?>
    </div>
    <h2>
        <?= $eventCount; ?> Event<?= $eventCount == 1 ? '' : 's'; ?> Contributed:
    </h2>

    <?= $this->element('events/accordion'); ?>

    <?php $this->Js->buffer("setupEventAccordion();"); ?>

</div>
