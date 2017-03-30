<?php
/*
	This displays complete information for a collection of events.
	$events can be for multiple days, i.e.
		$events[$date][$k] = $event
	or one day, i.e.
		$events[$k] = $event
*/
if (empty($events)) {
	$this->Js->buffer("setNoMoreEvents();");
} else {
	$event_keys = array_keys($events);
	$first_key = reset($event_keys);
	$multiple_dates = (strpos($first_key, '-') !== false);
	if ($multiple_dates) {
		foreach ($events as $date => $days_events) {
			echo $this->Calendar->dayHeaders($date);
			echo $this->element('events/accordion_day', array(
				'events' => $days_events
			));
		}
	} else {
		if (! isset($open_only_event)) {
			$open_only_event = false;
		}
		echo $this->element('events/accordion_day', array(
			'events' => $events,
			'open_only_event' => $open_only_event	// Open event if there's only one event
		));
	}
	if (isset($next_start_date)) {
		$this->Js->buffer("setNextStartDate('$next_start_date');");
	}
	$this->Js->buffer("setupEventAccordion();");
}
