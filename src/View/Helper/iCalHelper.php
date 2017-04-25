<?php
namespace App\View\Helper;

use Cake\Utility\Inflector;
use Cake\View\Helper;

require_once(ROOT.DS.'vendor'.DS.'iCalcreator.class.php');

class iCalHelper extends Helper
{
    public $name = 'ICalHelper';
    public $errorCode = null;
    public $errorMessage = null;

    public $calendar;

    public function create($name, $description='', $tz='US/Eastern')
    {
        $v = new vcalendar;
        $v->setConfig('unique_id', $name.'.'.'muncieevents.com');
        $v->setProperty('method', 'PUBLISH');
        $v->setProperty('x-wr-calname', $name.' Calendar');
        $v->setProperty("X-WR-CALDESC", $description);
        $v->setProperty("X-WR-TIMEZONE", $tz);

        $this->calendar = $v;
    }

    public function addEvent($start, $end=false, $summary, $description='', $extra=false)
    {
        $start = strtotime($start);

        $vevent = new vevent();
        if (!$end) {
            $end = $start + 24*60*60;
            $vevent->setProperty('dtstart', date('Ymd', $start), ['VALUE'=>'DATE']);
            $vevent->setProperty('dtend', date('Ymd', $end), ['VALUE'=>'DATE']);
        } else {
            $end = strtotime($end);
            $end = getdate($end);
            $end['sec'] = $end['seconds'];
            $end['hour'] = $end['hours'];
            $end['min'] = $end['minutes'];
            $end['month'] = $end['mon'];

            $start = getdate($start);
            $start['sec'] = $start['seconds'];
            $start['hour'] = $start['hours'];
            $start['min'] = $start['minutes'];
            $start['month'] = $start['mon'];

            // Editing, because setProperty() takes these parameters: $year, $month=FALSE, $day=FALSE, $hour=FALSE, $min=FALSE, $sec=FALSE, $tz=FALSE, $params=FALSE
            //$vevent->setProperty('dtstart', $start);
            $vevent->setProperty('dtstart', $start['year'], $start['mon'], $start['mday'], $start['hours'], $start['minutes']);
            //$vevent->setProperty('dtend', $end);
            $vevent->setProperty('dtend', $end['year'], $end['mon'], $end['mday'], $end['hours'], $end['minutes']);
        }
        $vevent->setProperty('summary', $summary);
        $vevent->setProperty('description', $description);
        if (is_array($extra)) {
            foreach ($extra as $key=>$value) {
                $vevent->setProperty($key, $value);
            }
        }
        $this->calendar->setComponent($vevent);
    }

    public function getCalendar()
    {
        return $this->calendar;
    }

    public function render($filename = null)
    {
        if ($filename) {
            $this->calendar->filename = $filename;
        }
        $this->calendar->returnCalendar();
    }
}
