<?php
    $this->iCal->create('Muncie Events', 'Muncie Events iCal export', 'US/Eastern');
    $this->element('events/add_ical');
    $filename = Inflector::slug($event->title);
    $filename .= date('-m-d-Y', strtotime($event->date));
    $this->iCal->render($filename.'.ics');
