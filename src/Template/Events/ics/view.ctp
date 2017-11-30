<?php
use App\Model\Table\EventsTable;
use Cake\Utility\Inflector;
use Eluceo\iCal;

$this->Events = new EventsTable();
$dst = $this->Events->getDaylightSavings($event->start->format('Y-m-d'));

$date = strtotime($event->start->i18nFormat('yyyyMMddHHmmss') . $dst);
$startTime = strtotime($event->start->i18nFormat('yyyyMMddHHmmss') . $dst);
if ($event->end) {
    $endTime = strtotime($event->end->i18nFormat('yyyyMMddHHmmss') . $dst);
}

$start = date('Ymd', $date).'T'.date('His', $startTime).'Z';

$endStamp = $startTime;
if ($event->end) {
    $endTime = strtotime($event->end->i18nFormat('yyyyMMddHHmmss') . $dst);
    $endStamp = $endTime;
}
$end = date('Ymd', $date).'T'.date('His', $endStamp).'Z';

$vCalendar = new \Eluceo\iCal\Component\Calendar('www.muncieevents.com');

$vEvent = new \Eluceo\iCal\Component\Event();
$vEvent
    ->setDtStart(new \Datetime($start))
    ->setDtEnd(new \Datetime($end))
    ->setDescriptionHTML($event->description)
    ->setLocation($event->location.' ('.$event->address.')')
    ->setUniqueId($event->id.'@muncieevents.com')
    ->setUrl('www.muncieevents.com/event/'.$event->id)
    ->setCategories($event->category->name);

$vCalendar
    ->setTimezone('US/Eastern')
    ->addComponent($vEvent);

echo $vCalendar->render();
