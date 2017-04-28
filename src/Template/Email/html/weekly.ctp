<style>
    <?php include(WWW_ROOT.'css'.DS.'email.css'); ?>
</style>

<h1>
    <a href="http://muncieevents.com">
        <img src="http://muncieevents.com/img/email_logo.png" alt="Muncie Events" />
    </a>
</h1>

<?php if ($welcome_message): ?>
    <p>
        <?= $this->Text->autoLink($welcome_message); ?>
    </p>
<?php endif; ?>

<div>
    <?php foreach ($events as $timestamp => $days_events): ?>
        <?php if (empty($days_events)) {
    continue;
} ?>
        <h3 class="day">
            <?= date('l', $timestamp).' <span class="date">'.date('F j', $timestamp).'<sup>'.date('S', $timestamp).'</sup></span>'; ?>
        </h3>
        <?php foreach ($days_events as $event): ?>
            <p class="event">
                <?= $this->Icon->category($event->Categories->name, 'email'); ?>

                <?= $this->Html->link(
                    $event->title,
                    Router::url([
                        'controller' => 'events',
                        'action' => 'view',
                        'id' => $event->id
                    ], true)
                ); ?>
                <br />
                <?= date('g:ia', strtotime($event->time_start)); ?>
                <?php if ($event->time_end): ?>
                    - <?= date('g:ia', strtotime($event->time_end)); ?>
                <?php endif; ?>
                @
                <?= $event->location; ?>
            </p>
        <?php endforeach; ?>
    <?php endforeach; ?>
</div>

<p class="footnote">
    <strong>Your settings...</strong><br />
    Frequency: <?= $settings_display['frequency']; ?><br />
    Events: <?= $settings_display['event_types']; ?>
</p>

<p class="footnote">
    This email was sent to <?= $recipient_email; ?>
    on behalf of <a href="http://muncieevents.com">MuncieEvents.com</a>
    <br />
    <?= $this->Html->link(
        'Add Event',
        Router::url([
            'controller' => 'events',
            'action' => 'add'
        ], true)
    ); ?>
    &nbsp; | &nbsp;
    <?= $this->Html->link(
        'Change Settings',
        Router::url([
            'controller' => 'mailing_list',
            'action' => 'settings',
            $recipient_id,
            $hash
        ], true)
    ); ?>
    &nbsp; | &nbsp;
    <?= $this->Html->link(
        'Unsubscribe',
        Router::url([
            'controller' => 'mailing_list',
            'action' => 'settings',
            $recipient_id,
            $hash,
            '?' => 'unsubscribe'
        ], true)
    ); ?>
</p>
