<?php
/**
 * @var \App\View\AppView $this
 */
	$this->iCal->create('Muncie Events', 'Muncie Events iCal export', 'US/Eastern');
    $this->element('events/add_ical');
    $filename = Inflector::slug($event['Event']['title']);
    $filename .= date('-m-d-Y', strtotime($event['Event']['date']));
    $this->iCal->render($filename.'.ics');