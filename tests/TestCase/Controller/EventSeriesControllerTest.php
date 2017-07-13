<?php
namespace App\Test\TestCase\Controller;

use App\Controller\EventSeriesController;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestCase;

/**
 * App\Controller\EventSeriesController Test Case
 */
class EventSeriesControllerTest extends IntegrationTestCase
{
    /**
     * test event add page when logged in and adding a series
     *
     * @return void
     */
    public function testAddingEventSeries()
    {
        $this->session(['Auth.User.id' => 74]);
        $this->get('/events/add');

        $this->assertResponseOk();

        $dates = [date('m/d/Y'), date('m/d/Y', strtotime("+1 day")), date('m/d/Y', strtotime("+2 days"))];
        $dates = implode(',', $dates);

        $rightNow = [
            'hour' => date('h'),
            'minute' => date('i'),
            'meridian' => date('a')
        ];

        $series = [
            'title' => 'Placeholder Event Series',
            'category_id' => 13,
            'date' => $dates,
            'time_start' => $rightNow,
            'time_end' => strtotime('+1 hour'),
            'location' => 'Mr. Placeholder\'s Place',
            'location_details' => 'Room 6',
            'address' => '666 Placeholder Place',
            'description' => 'Come out with my support!',
            'cost' => '$6',
            'age_restriction' => '66 or younger',
            'source' => 'Placeholder Digest Tri-Weekly'
        ];

        $this->post('/events/add', $series);
        $this->assertResponseSuccess();

        $this->EventSeries = TableRegistry::get('EventSeries');
        $series = $this->EventSeries->find()
            ->where(['title' => $series['title']])
            ->firstOrFail();
    }

    /**
     * test editing an event series
     *
     * @return void
     */
    public function testEditingEventSeries()
    {
        $this->EventSeries = TableRegistry::get('EventSeries');
        $series = $this->EventSeries->find()
            ->where(['title' => 'Placeholder Event Series'])
            ->firstOrFail();

        $this->Events = TableRegistry::get('Events');
        $events = $this->Events->find()
            ->where(['series_id' => $series->id]);

        $id = [];
        foreach ($events as $event) {
            $id[] = $event->id;
        }

        $this->session(['Auth.User.id' => 74]);

        $this->get("/event-series/edit/$series->id");

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
                    'id' => $id[0],
                    'time_start' => $rightNow
                ],
                1 => [
                    'date' => $today,
                    'delete' => 0,
                    'edited' => 1,
                    'id' => $id[1],
                    'time_start' => $rightNow,
                    'title' => 'Placeholder Party Series'
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
     * test editing an event series
     * using the edit series form from events controller
     *
     * @return void
     */
    public function testEditingEventSeriesFromEventsController()
    {
        $this->EventSeries = TableRegistry::get('EventSeries');
        $this->Events = TableRegistry::get('Events');
        $series = $this->EventSeries->find()
            ->where(['title' => 'Placeholder Event Series'])
            ->first();

        $this->session(['Auth.User.id' => 74]);

        $this->get("/event/editseries/$series->id");

        $this->assertResponseOk();

        $dates = [date('m/d/Y', strtotime("+1 day")), date('m/d/Y', strtotime("+2 days"))];
        $dates = implode(',', $dates);

        $rightNow = [
            'hour' => date('h'),
            'minute' => date('i'),
            'meridian' => date('a')
        ];

        $newSeries = [
            'title' => 'Placeholder Event Series',
            'category_id' => 13,
            'date' => $dates,
            'event_series' => [
                'title' => 'Placeholder Series'
            ],
            'time_start' => $rightNow,
            'time_end' => strtotime('+1 hour'),
            'location' => 'House of Placeholder',
            'location_details' => 'Room 6',
            'address' => '666 Placeholder Place',
            'description' => 'Come out with my support!',
            'cost' => '$6',
            'age_restriction' => '66 or younger',
            'source' => 'Placeholder Digest Tri-Weekly'
        ];

        $this->post("/event/editseries/$series->id", $newSeries);
        $this->assertResponseSuccess();

        $count = $this->Events->find()
            ->where(['series_id' => $series->id])
            ->count();

        if ($count == 2) {
            $this->assertResponseSuccess();
        }
    }

    /**
     * test deleting an event series
     *
     * @return void
     */
    public function testDeletingSeriesWhenLoggedIn()
    {
        $this->EventSeries = TableRegistry::get('EventSeries');
        $series = $this->EventSeries->find()
            ->where(['title' => 'Placeholder Series'])
            ->first();

        $this->session(['Auth.User.id' => 74]);

        $this->get("/event-series/edit/$series->id");

        $delete = [
            'title' => 'Placeholder Series',
            'delete' => 1
        ];

        $this->post("/event-series/edit/$series->id", $delete);

        $this->Events = TableRegistry::get('Events');
        $oldEvent = $this->Events->find()
            ->where(['title' => 'Placeholder Series'])
            ->first();
        $oldSeries = $this->EventSeries->find()
            ->where(['title' => 'Placeholder Series'])
            ->first();

        if (!$oldEvent & !$oldSeries) {
            $this->assertResponseSuccess();
        } else {
            $this->assertResponseError();
        }
    }
}
