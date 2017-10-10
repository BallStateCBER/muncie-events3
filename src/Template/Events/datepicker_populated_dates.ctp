<?php
    foreach ($dates as $monthYear => $days) {
        $this->Js->buffer('muncieEvents.populatedDates['.$monthYear.'] = ['.implode(',', $days).'];');
    }
