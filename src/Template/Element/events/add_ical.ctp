<?php
    // Times
    $start = $event->date.' '.$event->time_start;
    if ($event->time_end) {
        if ($event->time_start < $event->time_end) {
            $end = $event->date.' '.$event->time_end;
        } else {
            $end_date = date('Y-m-d', strtotime($event->date.' +1 day'));
            $end = $end_date.' '.$event->time_end;
        }
    } else {
        $end = $start;
    }

    $summary = $event->title;

    // Description
    $description = $event->description;
    $description = strip_tags($description);
    $description = str_replace('&nbsp;', '', $description);
    $description = Sanitize::clean($description, array(
        'odd_spaces', 'carriage'
    ));

    $extras = array();
    $extras['UID'] = $event->id.'@muncieevents.com';
    $extras['location'] = $event->location;
    if ($event->location_details) {
        $extras['location'] .= ', '.$event->location_details;
    }
    if (trim($event->address) != '') {
        $extras['location'] .= ' ('.trim($event->address).')';
    }
    $extras['categories'] = $event->category->name;
    if ($event->source) {
        $extras['comment'] = 'Info source: '.$event->source;
    }
    if ($event->user->email) {
        $extras['organizer'] = $event->user->email;
    }

    $this->iCal->addEvent(
        $start,
        $end,
        $summary,
        $description,
        $extras
    );
