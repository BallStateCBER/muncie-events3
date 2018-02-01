<?php
namespace App\Time;

class Time
{
    /**
     * Returns Muncie's UTC offset on the specified date
     *
     * @param string $date A date or datetime in a format that can be read by strtotime()
     * @return string
     */
    public function getUtcOffset($date)
    {
        return date('I', strtotime($date)) ? ' + 4 hours' : ' + 5 hours';
    }

    /**
     * Returns the supplied end time in 'Y-m-d H:i:s' format
     *
     * @param string $date A date that can be read by strtotime()
     * @param array $end An associative array of hour, minute, and meridian
     * @param array $start An associative array of hour, minute, and meridian
     * @return string
     */
    public function getEndUtc($date, $end, $start)
    {
        $utcOffset = $this->getUtcOffset($date);
        $startString = "$date {$start['hour']}:{$start['minute']} {$start['meridian']} $utcOffset";
        $endString = "$date {$end['hour']}:{$end['minute']} {$end['meridian']} $utcOffset";
        if (strtotime($startString) > strtotime($endString)) {
            $endString .= ' +1 day';
        }

        return new \Cake\I18n\Time(date('Y-m-d H:i:s', strtotime($endString)));
    }

    /**
     * Returns a Time object with the correct UTC offset
     *
     * @param string $date A date that can be read by strtotime()
     * @param array $start An associative array of hour, minute, and meridian
     * @return \Cake\I18n\Time
     */
    public function getStartUtc($date, $start)
    {
        $utcOffset = $this->getUtcOffset($date);
        $startString = "$date {$start['hour']}:{$start['minute']} {$start['meridian']} $utcOffset";

        return new \Cake\I18n\Time(date('Y-m-d H:i:s', strtotime($startString)));
    }
}
