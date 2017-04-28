Events for <?= $date; ?>

brought to you by http://MuncieEvents.com

<?php if ($welcome_message) {
    echo "$welcome_message\n\n";
} ?>

<?php
    foreach ($events as $event) {
        echo
            strtoupper($event->Categories->name).
            ': '.
            $event->title.
            "\n".
            '['.
            Router::url([
                'controller' => 'events',
                'action' => 'view',
                'id' => $event->id
            ], true).
            ']'.
            "\n".
            date('g:ia', strtotime($event->time_start));
        if ($event->time_end) {
            echo ' - '.date('g:ia', strtotime($event->time_end));
        }
        echo
            ' @ '.
            $event->location.
            "\n\n";
    }
?>

Your settings...
Frequency: <?= $settings_display['frequency']; ?>

Events: <?= $settings_display['event_types']; ?>


This email was sent to <?= $recipient_email; ?> on behalf of http://MuncieEvents.com

Add Event: <?= Router::url([
    'controller' => 'events',
    'action' => 'add'
], true); ?>

Change Settings: <?= Router::url([
    'controller' => 'mailing_list',
    'action' => 'settings',
    $recipient_id,
    $hash
], true); ?>

Unsubscribe: <?= Router::url([
    'controller' => 'mailing_list',
    'action' => 'settings',
    $recipient_id,
    $hash,
    '?' => 'unsubscribe'
], true); ?>
