<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Event[]|\Cake\Collection\CollectionInterface $events
 */

?>
<style>
    <?php include(WWW_ROOT . 'css' . DS . 'email.css'); ?>
</style>

<h1>
    <a href="<?= $fullBaseUrl ?>">
        <img src="<?= $fullBaseUrl ?>img/email_logo.png" alt="Muncie Events" />
    </a>
</h1>

<?php if ($welcome_message): ?>
    <p>
        <?= $this->Text->autoLink($welcome_message) ?>
    </p>
<?php endif; ?>

<div>
    <h3 class="day">
        <?= date('l') . ' <span class="date">' . date('F j') . '<sup>' . date('S') . '</sup></span>' ?>
    </h3>
    <?php foreach ($events as $event): ?>
        <p class="event">
            <?= $this->Icon->category($event->Categories['name'], 'email') ?>

            <?= $this->Html->link(
                $event->title,
                "https://www.muncieevents.com/$event->id"
            ) ?>
            <br />
            <?= date('g:ia', strtotime($event->time_start)) ?>
            <?php if ($event->time_end): ?>
                - <?= date('g:ia', strtotime($event->time_end)) ?>
            <?php endif; ?>
            @
            <?= $event->location ?>
        </p>
    <?php endforeach; ?>
</div>

<p class="footnote">
    <strong>Your settings...</strong><br />
    Frequency: <?= $settings_display['frequency'] ?><br />
    Events: <?= $settings_display['eventTypes'] ?>
</p>

<p class="footnote">
    This email was sent to <?= $recipient_email ?>
    on behalf of <a href="<?= $fullBaseUrl ?>">MuncieEvents.com</a>
    <br />
    <?= $this->Html->link(
        'Add Event',
        "https://www.muncieevents.com/events/add"
    ); ?>
    &nbsp; | &nbsp;
    <!?=/* $this->Html->link(
        'Change Settings',
        Router::url([
            'controller' => 'mailing_list',
            'action' => 'settings',
            $recipient_id,
            $hash
        ], true)
    ); */?>
    &nbsp; | &nbsp;
    <!?=/* $this->Html->link(
        'Unsubscribe',
        Router::url([
            'controller' => 'mailing_list',
            'action' => 'settings',
            $recipient_id,
            $hash,
            '?' => 'unsubscribe'
        ], true)
    ); */?>
</p>
