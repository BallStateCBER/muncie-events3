<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Event[]|\Cake\Collection\CollectionInterface $events
 */
?>
Events for <?= $date; ?>

brought to you by https://MuncieEvents.com

<?php if ($welcome_message) {
    echo "$welcome_message\n\n";
} ?>

<?php
    foreach ($events as $event) {
        echo
            strtoupper($event->Categories['name']).
            ': '.
            $event->title.
            "\n".
            '['.
            "https://www.muncieevents.com/$event->id".
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


This email was sent to <?= $recipient_email; ?> on behalf of https://MuncieEvents.com

Add Event: http://www.muncieevents.com/events/add
