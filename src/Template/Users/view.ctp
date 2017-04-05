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
	<?php if ($user->bio): ?>
		<h3>Bio</h3>
		<?= $user->bio ?>
	<?php else: ?>
	<?php endif; ?>

	<h2>
		<?= $eventCount; ?> Contributed Event<?= $eventCount == 1 ? '' : 's'; ?>:
	</h2>

	<?php foreach ($events as $event): ?>
		<?= $this->Calendar->dayHeaders($event->date); ?>
		<?= $this->element('events/accordion_day', [
            'event' => $event
        ]); ?>
	<?php endforeach; ?>

	<?php $this->Js->buffer("setupEventAccordion();"); ?>

</div>
