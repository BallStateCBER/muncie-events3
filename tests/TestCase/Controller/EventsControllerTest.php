<?php
namespace App\Test\TestCase\Controller;

use App\Controller\EventsController;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestCase;

/**
 * App\Controller\EventsController Test Case
 */
class EventsControllerTest extends IntegrationTestCase
{
    /**
     * test event add page when logged out
     *
     * @return void
     */
    public function testLoginRedirectWhenAddingEvents()
    {
        $this->get('/events/add');
        $this->assertRedirectContains('/login');
    }

    /**
     * test event add page when logged IN
     *
     * @return void
     */
    public function testAddingEvents()
    {
        $this->session(['Auth.User.id' => 554]);
        $this->get('/events/add');
        $this->assertResponseOk();

        $event = [
            'title' => 'Placeholder Party',
            'category_id' => 13,
            'date' => date('Y-m-d'),
            'time_start' => date('Y-m-d'),
            'time_end' => strtotime('+1 hour'),
            'location' => 'Mr. Placeholder\'s Place',
            'location_details' => 'Room 6',
            'address' => '666 Placeholder Place',
            'description' => 'Come out with my support!',
            'cost' => '$6',
            'age_restriction' => '66 or younger',
            'source' => 'Placeholder Digest Tri-Weekly'
        ];

        $this->post('/events/add', $event);
        $this->assertResponseOk();
        $this->assertResponseContains('The event has been saved.');

        $this->Events = TableRegistry::get('Events');
        $event = $this->Events->find()
            ->where(['title' => $event['title']])
            ->first();

        if ($event->id) {
            $this->assertResponseOk();
            return;
        }
        if (!$event->id) {
            $this->assertResponseError();
        }
    }

    /**
     * test deleting events
     *
     * @return void
     */
    public function testDeletingEvents()
    {
        $this->Events = TableRegistry::get('Events');
        $event = $this->Events->find()
            ->where(['title' => 'Placeholder Party'])
            ->first();

        $this->session(['Auth.User.id' => 1]);

        $this->get("/events/delete/$event->id");
        $this->assertResponseSuccess();
    }
}
