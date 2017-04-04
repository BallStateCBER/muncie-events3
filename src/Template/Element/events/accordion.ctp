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
	if ($multiple_dates) {
		foreach ($events as $event) {
			echo $this->Calendar->dayHeaders($event->date);
			echo $this->element('events/accordion_day', [
				'event' => $event
			]);
		}
	} else {
		if (! isset($open_only_event)) {
			$open_only_event = false;
		}
		echo $this->element('events/accordion_day', [
			'events' => $events,
			'open_only_event' => $open_only_event	// Open event if there's only one event
		]);
	}
	if (isset($next_start_date)) {
		$this->Js->buffer("setNextStartDate('$next_start_date');");
	}
	$this->Js->buffer("setupEventAccordion();");
}
