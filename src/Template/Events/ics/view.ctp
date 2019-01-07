<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Event $event
 */
use App\Model\Table\EventsTable;

$this->Events = new EventsTable();
$dst = $this->Events->getDaylightSavingOffsetPositive($event->date->format('Y-m-d'));

$date = strtotime($event->date->i18nFormat('yyyyMMddHHmmss') . $dst);
$startTime = strtotime($event->time_start->i18nFormat('yyyyMMddHHmmss') . $dst);
if ($event->time_end) {
    $endTime = strtotime($event->time_end->i18nFormat('yyyyMMddHHmmss') . $dst);
}

$start = date('Ymd', $date).'T'.date('His', $startTime).'Z';

$endStamp = $startTime;
if ($event->time_end) {
    $endTime = strtotime($event->time_end->i18nFormat('yyyyMMddHHmmss') . $dst);
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
