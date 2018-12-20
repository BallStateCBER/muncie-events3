<?php
/**
 * @var \App\View\AppView $this
 */

use Cake\Utility\Text;

$this->iCal->create('Muncie Events', 'Muncie Events iCal export', 'US/Eastern');
$this->element('events/add_ical');
$filename = Text::slug($event['Event']['title']);
$filename .= date('-m-d-Y', strtotime($event['Event']['date']));
$this->iCal->render($filename . '.ics');
