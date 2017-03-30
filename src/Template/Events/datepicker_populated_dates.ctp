<?php 
	foreach ($dates as $month_year => $days) {
		$this->Js->buffer('muncieEvents.populatedDates['.$month_year.'] = ['.implode(',', $days).'];');
	}
