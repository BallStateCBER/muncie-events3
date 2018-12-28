<?php
namespace App\Test\TestCase\Controller;

use App\Test\TestCase\ApplicationTest;

class EventSeriesControllerTest extends ApplicationTest
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
     * test editing an event series
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testEditingEventSeries()
    {
        $series = $this->EventSeries->find()->firstOrFail();

        // first, as just anybody
        $this->get("/event-series/edit/$series->id");
        $this->assertRedirect("/login?redirect=%2Fevent-series%2Fedit%2F$series->id");

        // how about a non-owner?
        $this->session($this->commoner);
        $this->get("/event-series/edit/$series->id");
        $this->assertRedirect('/');

        $this->session($this->admin);
        $this->get("/event-series/edit/$series->id");
        $this->assertResponseOk();
        $this->assertResponseContains('Edit Series');

        $today = [
            'year' => date('Y'),
            'month' => date('m'),
            'day' => date('d')
        ];
        $rightNow = [
            'hour' => date('h'),
            'minute' => date('i'),
            'meridian' => date('a')
        ];
        $edits = [
            'events' => [
                0 => [
                    'date' => $today,
                    'delete' => 1,
                    'edited' => 1,
                    'id' => 0,
                    'time_start' => $rightNow,
                    'title' => 'Placeholder Event From Long Ago'
                ],
                1 => [
                    'date' => $today,
                    'delete' => 0,
                    'edited' => 1,
                    'id' => 1,
                    'time_start' => $rightNow,
                    'title' => 'Placeholder Party Series'
                ],
                2 => [
                    'date' => $today,
                    'delete' => 0,
                    'edited' => 1,
                    'id' => 2,
                    'time_start' => $rightNow,
                    'title' => 'Placeholder Event Series'
                ],
                3 => [
                    'date' => $today,
                    'delete' => 0,
                    'edited' => 1,
                    'id' => 2,
                    'time_start' => $rightNow,
                    'title' => 'Placeholder Event Series'
                ],
                4 => [
                    'date' => $today,
                    'delete' => 1,
                    'edited' => 1,
                    'id' => 2,
                    'time_start' => $rightNow,
                    'title' => 'Placeholder Event Series'
                ]
            ],
            'title' => 'Placeholder Event Series',
            'delete' => 0
        ];
        $this->post("/event-series/edit/$series->id", $edits);
        $newEvent = $this->Events->find()
            ->where(['title' => 'Placeholder Party Series'])
            ->firstOrFail();

        if ($newEvent->id) {
            $this->assertResponseSuccess();
        }
    }

    /**
     * test viewing an event series
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testViewingEventSeries()
    {
        $series = $this->EventSeries->find()->firstOrFail();
        $this->get("/event_series/$series->id");
        $this->assertResponseOk();
        $this->assertResponseContains($series['title']);
    }
}
