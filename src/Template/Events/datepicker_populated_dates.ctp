<?php
    foreach ($calDates as $monthYear => $days) {
        $this->Js->buffer('muncieEvents.populatedDates['.$monthYear.'] = ['.implode(',', $days).'];');
    }
    if (php_sapi_name() == 'cli') {
        print 'Yep, this one works.';
    }