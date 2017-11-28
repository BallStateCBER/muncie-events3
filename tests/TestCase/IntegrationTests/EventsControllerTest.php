<?php
namespace App\Test\TestCase\Controller;

use App\Test\TestCase\ApplicationTest;
use Cake\Utility\Text;

class EventsControllerTest extends ApplicationTest
{
    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();
    }

    /**
     * test how events get approved
     *
     * @return void
     */
    public function testApprovalSystem()
    {
        $this->session($this->commoner);
        $this->get('/moderate');

        $this->assertRedirect('/');

        $this->session($this->admin);
        $this->session(['Auth.User.id' => 1]);
        $this->get('/moderate');
        $this->assertResponseOk();

        $this->assertResponseContains('/events/approve/1/2/3');
        $this->get('/events/approve/1/2/3');

        $event = $this->Events->get(1);
        $this->assertEquals(1, $event->published);
    }

    /**
     * test adding new events
     *
     * @return void
     */
    public function testAddingEvents()
    {
        // first, with a non-user
        $this->get('/events/add');
        $this->assertResponseOk();
        $this->assertResponseContains('You can still submit this event, but...');

        $newData = [
            'title' => 'Anonymous event!',
            'category_id' => 1,
            'date' => date('m/d/Y'),
            'time_start' => [
                'hour' => '12',
                'minute' => '00',
                'meridian' => 'am'
            ],
            'time_end' => [
                'hour' => '01',
                'minute' => '00',
                'meridian' => 'am'
            ],
            'has_end_time' => 1,
            'location' => 'Mr. Placeholder\'s Place',
            'location_details' => 'Room 6',
            'address' => '666 Placeholder Place',
            'description' => 'Come out with my support!',
            'cost' => '$6',
            'age_restriction' => '66 or younger',
            'source' => 'Placeholder Digest Tri-Weekly',
            'data' => [
                'Tags' => [
                    1
                ]
            ]
        ];

        $this->post('/events/add', $newData);
        $this->assertResponseSuccess();

        $event = $this->Events->find()
            ->where(['title' => $newData['title']])
            ->contain(['Tags'])
            ->firstOrFail();

        $this->assertEquals(0, $event['published']);
        foreach ($event['tags'] as $tag) {
            $this->assertEquals(1, $tag['id']);
        }

        // now with a common user
        $this->session($this->commoner);
        $this->get('/events/add');
        $this->assertResponseOk();

        $newData = [
            'title' => 'Wow, I can add custom tags!',
            'category_id' => 1,
            'date' => date('m/d/Y'),
            'time_start' => [
                'hour' => '12',
                'minute' => '00',
                'meridian' => 'pm'
            ],
            'time_end' => [
                'hour' => '01',
                'minute' => '00',
                'meridian' => 'pm'
            ],
            'has_end_time' => 1,
            'location' => 'Mr. Placeholder\'s Place',
            'location_details' => 'Room 6',
            'address' => '666 Placeholder Place',
            'description' => 'Oh boy I can add custom tags wow!',
            'cost' => '$6',
            'age_restriction' => '66 or younger',
            'source' => 'Placeholder Digest Tri-Weekly',
            'data' => [
                'Tags' => [
                    1
                ]
            ],
            'customTags' => 'adding custom tags too'
        ];

        $this->post('/events/add', $newData);
        $this->assertResponseSuccess();

        $event = $this->Events->find()
            ->where(['title' => $newData['title']])
            ->contain(['Tags'])
            ->firstOrFail();

        $this->assertEquals(count($event['tags']), 2);
    }

    /**
     * test the category index
     *
     * @return void
     */
    public function testCategoryIndex()
    {
        $categories = $this->Categories->find();
        foreach ($categories as $category) {
            $this->get("/$category->slug");
            $this->assertResponseOk();
            $this->assertResponseContains($category->name);
        }
    }

    /**
     * test the datepicker
     *
     * @return void
     */
    public function testDatepickerFunctionWorks()
    {
        $this->get('/events/datepicker-populated-dates');
        $this->assertResponseContains('Yep');
    }

    /**
     * test day index
     *
     * @return void
     */
    public function testDayIndex()
    {
        $date = date('m-d-Y');
        $date = explode('-', $date);
        $this->get("/events/day/$date[0]/$date[1]/$date[2]");
        $this->assertResponseOk();
        $this->assertResponseContains('Events on ');
        $this->assertResponseContains($this->eventInSeries1['title']);
    }

    /**
     * test delete
     *
     * @return void
     */
    public function testDelete()
    {
        $event = $this->Events->find()->firstOrFail();

        // first, as just anybody
        $this->get("/events/delete/$event->id");
        $this->assertRedirect('/login?redirect=%2Fevents%2Fdelete%2F1');

        // can't do it can you? what if we register?
        $this->session($this->commoner);
        $this->get("/events/delete/$event->id");
        $this->assertRedirect('/');
        $deleted = $this->Events->find()
            ->where(['id' => $event->id])
            ->first();
        $this->assertEquals($event->id, $deleted->id);

        // nope! we must be an admin!
        $this->session($this->admin);
        $this->get("/events/delete/$event->id");
        $deleted = $this->Events->find()
            ->where(['id' => $event->id])
            ->first();
        $this->assertEquals(null, $deleted);
    }

    /**
     * test editing the events
     *
     * @return void
     */
    public function testEditingEvents()
    {
        $event = $this->Events->find()->firstOrFail();

        // first, as just anybody
        $this->get("/events/edit/$event->id");
        $this->assertRedirect("/login?redirect=%2Fevents%2Fedit%2F$event->id");

        // how about a non-owner?
        $this->session($this->commoner);
        $this->get("/events/edit/$event->id");
        $this->assertRedirect('/');

        $this->session($this->admin);
        $this->get("/events/edit/$event->id");
        $this->assertResponseOk();
        $this->assertResponseContains('Edit Event');

        $formData = [
            'title' => 'Placeholder Event Series Special Edition',
            'description' => 'This event is crazy special for some reason!',
            'location' => 'Be Here Now',
            'address' => '505 N. Dill St.',
            'user_id' => 1,
            'category_id' => 2,
            'series_id' => 1,
            'date' => date('Y-m-d', strtotime('+1 day')),
            'time_start' => [
                'hour' => '12',
                'minute' => '00',
                'meridian' => 'am'
            ],
            'time_end' => [
                'hour' => '01',
                'minute' => '00',
                'meridian' => 'am'
            ]
        ];

        $this->post("/events/edit/$event->id", $formData);
        $this->assertResponseSuccess();

        $newEvent = $this->Events->find()
            ->where(['title' => $formData['title']])
            ->firstOrFail();

        $this->assertEquals($newEvent->id, $event->id);
    }

    /**
     * test editing the event series
     *
     * @return void
     */
    public function testEditingSeries()
    {
        $series = $this->EventSeries->find()->firstOrFail();

        // first, as just anybody
        $this->get("/events/edit-series/$series->id");
        $this->assertRedirect("/login?redirect=%2Fevents%2Fedit-series%2F$series->id");

        // how about a non-owner?
        $this->session($this->commoner);
        $this->get("/events/edit-series/$series->id");
        $this->assertRedirect('/');

        $this->session($this->admin);
        $this->get("/events/edit-series/$series->id");
        $this->assertResponseOk();
        $this->assertResponseContains('Edit Event Series:');

        $formData = [
            'title' => 'Regular Placeholder Event Series',
            'description' => 'We are a regular event series we are uniform!',
            'series_title' => 'Totally Normal Placeholder Series',
            'location' => 'Be Here Now',
            'address' => '505 N. Dill St.',
            'user_id' => 1,
            'category_id' => 2,
            'series_id' => 1,
            'date' => date('Y-m-d', strtotime('+1 day')),
            'time_start' => [
                'hour' => '12',
                'minute' => '00',
                'meridian' => 'am'
            ],
            'time_end' => [
                'hour' => '01',
                'minute' => '00',
                'meridian' => 'am'
            ]
        ];

        $this->post("/events/edit-series/$series->id", $formData);
        $this->assertResponseSuccess();

        $newSeries = $this->EventSeries->find()
            ->where(['title' => $formData['series_title']])
            ->firstOrFail();

        $this->assertEquals($newSeries->id, $series->id);
    }

    /**
     * test getting events addresses for ajax
     *
     * @return void
     */
    public function testGettingEventsAddresses()
    {
        $this->get('/events/get-address/placeholder%20place');
        $this->assertResponseOk();
        $this->assertResponseContains('505 N. Bill St.');
    }

    /**
     * test events index
     *
     * @return void
     */
    public function testEventsIndex()
    {
        $event = $this->Events->find()->firstOrFail();
        $this->get('/');
        $this->assertResponseOk();
        $this->assertResponseContains('Today');
        $this->assertResponseContains('Tomorrow');
        $this->assertResponseContains($event['title']);
    }

    /**
     * test locations index
     *
     * @return void
     */
    public function testLocationsIndex()
    {
        $event = $this->Events->find()
            ->where(['location' => 'Placeholder Place'])
            ->first();
        $this->get('/location/Placeholder%20Place');
        $this->assertResponseOk();
        $this->assertResponseContains($event['title']);
        $this->assertResponseContains('Placeholder Place');
    }

    /**
     * test months index
     *
     * @return void
     */
    public function testMonthsIndex()
    {
        $month = date('m/Y');
        $event = $this->Events->find()->firstOrFail();
        $this->get("/events/month/$month");
        $this->assertResponseOk();
        $this->assertResponseContains(date('l'));
        $this->assertResponseContains($event['title']);
    }

    /**
     * test index of past locations
     *
     * @return void
     */
    public function testPastLocations()
    {
        $this->get("/events/past-locations");
        $this->assertResponseOk();
        $this->assertResponseContains('Placeholder Place');
    }

    /**
     * test search function
     *
     * @return void
     */
    public function testSearch()
    {
        $this->get("/search?filter=be+here+now&direction=future");
        $this->assertResponseOk();
        $this->assertResponseContains(date('l'));
        $this->assertResponseContains('Be Here Now');
    }

    /**
     * test search autocomplete populate for ajax
     *
     * @return void
     */
    public function testSearchAutocomplete()
    {
        $this->get("/events/search_auto_complete/all?term=holding");
        $this->assertResponseOk();
        $this->assertResponseContains('{"1":"holding places"');
    }

    /**
     * test tag index
     *
     * @return void
     */
    public function testTagIndex()
    {
        $tag = $this->Tags->find()->firstOrFail();
        $slug = $tag['id'] . '_' . Text::slug($tag['name']);
        $this->get("/tag/$slug/upcoming");
        $this->assertResponseOk();
        $name = ucwords($tag['name']);
        $this->assertResponseContains("with Tag: $name");
    }

    /**
     * test the today & tomorrow redirects
     *
     * @return void
     */
    public function testTodayAndTomorrow()
    {
        $todayString = date('m/d/Y');
        $tomorrowString = date('m/d/Y', strtotime('+1 day'));

        $this->get('/events/today');
        $this->assertRedirect("/events/day/$todayString");

        $this->get('/events/tomorrow');
        $this->assertRedirect("/events/day/$tomorrowString");
    }

    /**
     * let's end on an easy one.
     * test individual event views
     *
     * @return void
     */
    public function testEventView()
    {
        $event = $this->Events->find()->firstOrFail();
        $this->get("/event/" . $event['id']);
        $this->assertResponseOk();
        $this->assertResponseContains($event['title']);
        $this->assertResponseContains($event['description']);
    }
}
