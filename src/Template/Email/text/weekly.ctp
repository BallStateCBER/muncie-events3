Upcoming Events
brought to you by https://MuncieEvents.com

<?php if ($welcome_message) {
    echo "$welcome_message\n\n";
} ?>

<?php
    foreach ($events as $timestamp => $days_events) {
        if (empty($days_events)) {
            continue;
        }
        echo date('l', $timestamp).', '.date('F jS', $timestamp)."\n--------------\n";
        foreach ($days_events as $event) {
            echo
                strtoupper($event->Categories->name).
                ': '.
                $event->title.
                "\n".
                date('g:ia', strtotime($event->time_start));
            if ($event->time_end) {
                echo ' - '.date('g:ia', strtotime($event->time_end));
            }
            echo
                ' @ '.
                $event->location.
                "\n".
                Router::url([
                    'controller' => 'events',
                    'action' => 'view',
                    'id' => $event->id
                ], true).
                "\n\n";
        }
        echo "\n\n";
    }
?>

Your settings...
Frequency: <?= $settings_display['frequency']; ?>

Events: <?= $settings_display['event_types']; ?>


This email was sent to <?= $recipient_email; ?> on behalf of https://MuncieEvents.com

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
