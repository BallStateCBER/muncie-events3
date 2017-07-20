<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\EventsTable;
use Cake\I18n\Date;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\EventsTable Test Case
 */
class EventsTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Table\EventsTable
     */
    public $Events;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('Events') ? [] : ['className' => 'App\Model\Table\EventsTable'];
        $this->Events = TableRegistry::get('Events', $config);
        $this->EventsTags = TableRegistry::get('EventsTags');
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Events);
        unset($this->EventsTags);

        parent::tearDown();
    }

    public function testGetEventsOnDay()
    {
        $date = new Date(date('Y-m-d'));
        $events = $this->Events->getEventsOnDay(date('Y'), date('m'), date('d'));
        foreach ($events as $event) {
            $this->assertEquals($event->date, $date);
        }
    }

    /**
     * Test getUpcomingEvents method
     *
     * @return void
     */
    public function testGetUpcomingEvents()
    {
        $event = $this->Events->newEntity();
        $event->title = 'Anonymous';
        $event->category_id = 13;
        $event->date = date('Y-m-d');
        $event->time_start = strtotime('Now');
        $event->location = 'Placeholder palace';
        $event->description = 'Unit testing sure is boring';
        $this->Events->save($event);

        $joinData = $this->EventsTags->newEntity();
        $joinData->event_id = $event->id;
        $joinData->tag_id = 692;
        $this->EventsTags->save($joinData);

        $date = new Date(date('Y-m-d'));

        $events = $this->Events->getUpcomingEvents();
        $this->assertEquals($date, $events[0]->date);

        $this->Events->delete($event);
    }

    /**
     * Test getUpcomingFilteredEvents method
     *
     * @return void
     */
    public function testgetUpcomingFilteredEvents()
    {
        // the only event that should make it out of the filter
        $event = $this->Events->newEntity();
        $event->title = 'Best friend by Yelawolf on repeat';
        $event->category_id = 13;
        $event->date = '2020-01-01';
        $event->time_start = strtotime('Now');
        $event->location = 'Placeholder palace';
        $event->description = 'Unit testing sure is boring';
        $this->Events->save($event);

        $joinData = $this->EventsTags->newEntity();
        $joinData->event_id = $event->id;
        $joinData->tag_id = 692;
        $this->EventsTags->save($joinData);

        // wrong category
        $event = $this->Events->newEntity();
        $event->title = 'Best friend by Yelawolf on repeat';
        $event->category_id = 8;
        $event->date = '2020-01-28';
        $event->time_start = strtotime('Now');
        $event->location = 'Placeholder palace';
        $event->description = 'Unit testing sure is boring';
        $this->Events->save($event);

        $joinData = $this->EventsTags->newEntity();
        $joinData->event_id = $event->id;
        $joinData->tag_id = 692;
        $this->EventsTags->save($joinData);

        // wrong location
        $event = $this->Events->newEntity();
        $event->title = 'Best friend by Yelawolf on repeat';
        $event->category_id = 13;
        $event->date = '2020-01-28';
        $event->time_start = strtotime('Now');
        $event->location = 'Placeholder parce';
        $event->description = 'Unit testing sure is boring';
        $this->Events->save($event);

        $joinData = $this->EventsTags->newEntity();
        $joinData->event_id = $event->id;
        $joinData->tag_id = 692;
        $this->EventsTags->save($joinData);

        // un-included tag
        $event = $this->Events->newEntity();
        $event->title = 'Best friend by Yelawolf on repeat';
        $event->category_id = 13;
        $event->date = '2020-01-28';
        $event->time_start = strtotime('Now');
        $event->location = 'Placeholder palace';
        $event->description = 'Unit testing sure is boring';
        $this->Events->save($event);

        $joinData = $this->EventsTags->newEntity();
        $joinData->event_id = $event->id;
        $joinData->tag_id = 21;
        $this->EventsTags->save($joinData);

        // excluded tag
        $event = $this->Events->newEntity();
        $event->title = 'Best friend by Yelawolf on repeat';
        $event->category_id = 13;
        $event->date = '2020-01-28';
        $event->time_start = strtotime('Now');
        $event->location = 'Placeholder palace';
        $event->description = 'Unit testing sure is boring';
        $this->Events->save($event);

        $joinData = $this->EventsTags->newEntity();
        $joinData->event_id = $event->id;
        $joinData->tag_id = 1020;
        $this->EventsTags->save($joinData);

        // the only event that meets these criteria should be on this date, specifically
        $date = new Date('2020-01-01');

        $options = [
            'category' => 13,
            'location' => 'Placeholder palace',
            'tags_included' => 'acoustic music',
            'tags_excluded' => 'adult oriented'
        ];

        $events = $this->Events->getUpcomingFilteredEvents($options);
        $this->assertEquals($date, $events[0]->date);
    }

    public function testGetUnapproved()
    {
        $count = $this->Events->getUnapproved();

        $this->assertEquals(5, $count);
    }

    public function testGetNextStartDate()
    {
        $dates = [
            date('Y-m-d'),
            date('Y-m-d'),
            date('Y-m-d'),
            '2020-01-31',
            '2020-02-01'
        ];

        $lastDate = $this->Events->getNextStartDate($dates);
        $this->assertEquals('20200202', $lastDate);
    }

    public function testGetPrevStartDate()
    {
        $dates = [
            date('Y-m-d'),
            date('Y-m-d'),
            date('Y-m-d'),
            '2020-01-31',
            '2020-02-01'
        ];

        $prevDate = $this->Events->getPrevStartDate($dates);
        $this->assertEquals(date('Ymd', strtotime('Yesterday')), $prevDate);
    }

    public function testGetLocations()
    {
        $locations = $this->Events->getLocations();

        $this->assertContains('Placeholder parce', $locations);
    }

    public function testGetPastLocations()
    {
        $event = $this->Events->newEntity();
        $event->title = 'Best friend by Yelawolf on repeat';
        $event->category_id = 13;
        $event->date = '1992-01-28';
        $event->time_start = strtotime('Now');
        $event->location = 'Placeholder palace';
        $event->description = 'Unit testing sure is boring';
        $this->Events->save($event);

        $joinData = $this->EventsTags->newEntity();
        $joinData->event_id = $event->id;
        $joinData->tag_id = 1710;
        $this->EventsTags->save($joinData);

        $locations = $this->Events->getPastLocations();

        $this->assertContains('Placeholder palace', $locations);
    }

    public function testGetAllUpcomingEventCounts()
    {
        $events = $this->Events->getAllUpcomingEventCounts();

        $generalEvents = $this->Events->find()
            ->where(['category_id' => 13])
            ->andWhere(['date >=' => date('Y-m-d')])
            ->count();

        $this->assertEquals($generalEvents, $events[13]);
    }

    public function testGetCountInDirectionWithTag()
    {
        $event = $this->Events->newEntity();
        $event->title = 'Best friend by Yelawolf on repeat';
        $event->category_id = 13;
        $event->date = '2019-01-28';
        $event->time_start = strtotime('Now');
        $event->location = 'Placeholder palace';
        $event->description = 'Unit testing sure is boring';
        $this->Events->save($event);

        $joinData = $this->EventsTags->newEntity();
        $joinData->event_id = $event->id;
        $joinData->tag_id = 1710;
        $this->EventsTags->save($joinData);

        $tags = $this->Events->getCountInDirectionWithTag('future', 1710);

        $this->assertEquals($tags, 1);
    }

    public function testGetCountPastWithTag()
    {
        $tags = $this->Events->getCountPastWithTag(1710);
        $this->assertEquals($tags, 2);
    }

    public function testGetCountUpcomingWithTag()
    {
        $tags = $this->Events->getCountUpcomingWithTag(1710);
        $this->assertEquals($tags, 1);
    }

    public function testGetPastEventIds()
    {
        $myBirthday = new Date('1992-01-28');

        $event = $this->Events->find()
            ->where(['title' => 'Best friend by Yelawolf on repeat'])
            ->andWhere(['date' => $myBirthday])
            ->first();

        $eventIds = $this->Events->getPastEventIds();
        $this->assertContains($event->id, $eventIds);
    }

    public function testGetFutureEventIds()
    {
        $theFuture = new Date('2020-01-01');

        $event = $this->Events->find()
            ->where(['title' => 'Best friend by Yelawolf on repeat'])
            ->andWhere(['date' => $theFuture])
            ->first();

        $eventIds = $this->Events->getFutureEventIds();
        $this->assertContains($event->id, $eventIds);
    }

    public function testGetFutureEvents()
    {
        $theFuture = [
            0 => 'Tuesday',
            1 => 'Jan',
            2 => '01',
            3 => '28',
            4 => '2020'
        ];
        $events = $this->Events->getFutureEvents();

        $this->assertContains($theFuture, $events);
    }

    public function testGetIdsFromTag()
    {
        $eventIds = $this->Events->getIdsFromTag(1710);
        $event = $this->Events->find()
            ->where(['id IN' => $eventIds])
            ->first();

        $this->assertEquals(4183, $event->id);

        $dummyEvents = $this->Events->find()
            ->where(['title' => 'Best friend by Yelawolf on repeat']);

        foreach ($dummyEvents as $event) {
            $joinData = $this->EventsTags->find()
                ->where(['event_id' => $event->id])
                ->first();
            $this->EventsTags->delete($joinData);
            $this->Events->delete($event);
        }
    }

    public function testGetValidFilters()
    {
        $options = [
            'category' => 13,
            'location' => 'Placeholder palace',
            'tags_included' => 'acoustic music',
            'tags_excluded' => 'adult oriented'
        ];

        $filters = $this->Events->getValidFilters($options);

        $assumedFilters = [
            'location' => 'Placeholder palace',
            'tags_included' => [
                0 => 'acoustic music'
            ],
            'tags_excluded' => [
                0 => 'adult oriented'
            ]
        ];

        $this->assertEquals($filters, $assumedFilters);
    }
}
