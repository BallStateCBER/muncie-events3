<?php
namespace App\Test\TestCase\Controller;

use App\Test\TestCase\ApplicationTest;

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
        $category = $this->Categories->get(1);
        $this->get("/$category->slug");
        $this->assertResponseOk();
        $this->assertResponseContains($category->name);
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
        $event = $this->Events->find()->first();

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
}
