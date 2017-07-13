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
    public function testAddingEventAnonymously()
    {
        $this->get('/events/add');
        $this->assertResponseOk();

        $event = [
            'title' => 'Anonymous event!',
            'category_id' => 13,
            'date' => date('m/d/Y'),
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
        $this->assertResponseSuccess();

        $this->Events = TableRegistry::get('Events');
        $event = $this->Events->find()
            ->where(['title' => $event['title']])
            ->first();

        if ($event->id) {
            $this->assertResponseSuccess();
            $this->Events->delete($event);
            return;
        }
        if (!$event->id) {
            $this->assertResponseError();
        }
    }

    /**
     * test moderation page when user is not admin
     *
     * @return void
     */
    public function testModeratePageWhenUnauthorized()
    {
        $this->session(['Auth.User.id' => 74]);

        $this->get('/moderate');

        $this->assertRedirect('/login?redirect=%2Fmoderate');
    }

    /**
     * test event add page when logged IN
     *
     * @return void
     */
    public function testAddingEvents()
    {
        $this->session(['Auth.User.id' => 74]);
        $this->get('/events/add');
        $this->assertResponseOk();

        $event = [
            'title' => 'Placeholder Party',
            'category_id' => 13,
            'date' => date('m/d/Y'),
            'time_start' => date('Y-m-d'),
            'time_end' => strtotime('+1 hour'),
            'location' => 'Mr. Placeholder\'s Place',
            'location_details' => 'Room 6',
            'address' => '666 Placeholder Place',
            'description' => 'Come out with my support!',
            'cost' => '$6',
            'age_restriction' => '66 or younger',
            'source' => 'Placeholder Digest Tri-Weekly',
            'data' => [
                'Tags' => [
                    752
                ]
            ]
        ];

        $this->post('/events/add', $event);
        $this->assertResponseSuccess();

        $this->Events = TableRegistry::get('Events');
        $this->EventsTags = TableRegistry::get('EventsTags');
        $event = $this->Events->find()
            ->where(['title' => $event['title']])
            ->first();

        if ($event->id) {
            $tags = $this->EventsTags->find()
                ->where(['tag_id' => 752])
                ->andWhere(['event_id' => $event->id])
                ->first();

            if ($tags) {
                $this->assertResponseSuccess();
                return;
            }
        }
        if (!$event->id) {
            $this->assertResponseError();
        }
    }

    /**
     * test event add when an admin
     * to make sure the event is automatically published
     * or, at the very least, not automatically approved and then not published
     *
     * @return void
     */
    public function testAddingEventsWhenAnAdmin()
    {
        $this->session(['Auth.User.id' => 1]);
        $this->session(['Auth.User.role' => 'admin']);
        $this->get('/events/add');
        $this->assertResponseOk();

        $event = [
            'title' => 'Get better soon Jessica!',
            'category_id' => 13,
            'date' => date('m/d/Y'),
            'time_start' => date('Y-m-d'),
            'time_end' => strtotime('+1 hour'),
            'location' => 'Nashville',
            'location_details' => 'Tennessee',
            'address' => '666 Placeholder Place',
            'description' => 'Hope JLM feels better from her surgery!',
            'cost' => '$6',
            'age_restriction' => '66 or younger',
            'source' => 'Insta'
        ];

        $this->post('/events/add', $event);
        $this->assertResponseSuccess();

        $this->Events = TableRegistry::get('Events');
        $this->EventsTags = TableRegistry::get('EventsTags');
        $event = $this->Events->find()
            ->where(['title' => $event['title']])
            ->andWhere(['published' => 1])
            ->firstOrFail();

        if ($event->id) {
            $this->assertResponseSuccess();
            $this->get("/events/delete/$event->id");
            return;
        }
        if (!$event->id) {
            $this->assertResponseError();
        }
    }

    /**
     * test approving/publishing events
     * from an admin account
     *
     * @return void
     */
    public function testApprovingAndPublishingEvents()
    {
        $this->Events = TableRegistry::get('Events');
        $event = $this->Events->find()
            ->where(['title' => 'Placeholder Party'])
            ->firstOrFail();

        $this->session(['Auth.User.id' => 1]);
        $this->get('/moderate');
        $this->assertResponseSuccess();

        $this->get("/event/approve/$event->id");
        $this->assertResponseSuccess();
    }

    /**
     * test event page view
     *
     * @return void
     */
    public function testEventViewAfterApproval()
    {
        $this->Events = TableRegistry::get('Events');
        $event = $this->Events->find()
            ->where(['title' => 'Placeholder Party'])
            ->firstOrFail();

        $this->get("/event/$event->id");

        $this->assertResponseOk();
        $this->assertResponseContains($event->title);
        $this->assertResponseContains($event->description);
        $this->assertResponseContains($event->location);
    }

    /**
     * test editing events
     *
     * @return void
     */
    public function testEditingAnEventAsEventOwner()
    {
        $this->Events = TableRegistry::get('Events');
        $this->EventsTags = TableRegistry::get('EventsTags');
        $this->Tags = TableRegistry::get('Tags');
        $event = $this->Events->find()
            ->where(['title' => 'Placeholder Party'])
            ->firstOrFail();

        $this->session(['Auth.User.id' => 74]);

        $this->get("/event/edit/$event->id");

        $this->assertResponseContains($event->title);

        $randomNumber = rand();

        $edits = [
            'title' => 'Placeholder Gala',
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
            'source' => 'Placeholder Digest Tri-Weekly',
            'data' => [
                'Tags' => [
                    665
                ]
            ],
            'customTags' => $randomNumber
        ];

        $this->post("/event/edit/$event->id", $edits);
        $this->assertResponseSuccess();

        $event = $this->Events->find()
            ->where(['title' => 'Placeholder Gala'])
            ->firstOrFail();

        $customTag = $this->Tags->find()
            ->where(['name' => $randomNumber])
            ->firstOrFail();

        $tags = $this->EventsTags->find()
            ->where(['tag_id' => 665])
            ->andWhere(['event_id' => $event->id])
            ->orWhere(['tag_id' => $customTag->id])
            ->firstOrFail();
    }

    /**
     * test editing events the user doesn't own
     *
     * @return void
     */
    public function testEditingAnEventAsNonOwner()
    {
        $this->Events = TableRegistry::get('Events');
        $event = $this->Events->find()
            ->where(['title' => 'Placeholder Gala'])
            ->firstOrFail();

        $this->session(['Auth.User.id' => 75]);

        $this->get("/event/edit/$event->id");
        $this->assertRedirect('/');
    }

    /**
     * test deleting events
     * when the user isn't authorized to do that in any way
     *
     * @return void
     */
    public function testDeletingEventsWhenUserIsNotAuthorized()
    {
        $this->Events = TableRegistry::get('Events');
        $event = $this->Events->find()
            ->where(['title' => 'Placeholder Gala'])
            ->firstOrFail();

        $this->session(['Auth.User.id' => 72]);

        $this->get("/events/delete/$event->id");
        $this->assertRedirect('/');

        // but the event still exists, right?
        if ($event) {
            $this->assertResponseSuccess();
        }

        if (!$event) {
            $this->assertResponseError();
        }
    }

    /**
     * test searching for events events
     *
     * @return void
     */
    public function testSearchingForEvents()
    {
        $this->get('/');
        $query = [
            'filter' => 'Placeholder Gala',
            'direction' => 'future'
        ];
        $this->post('/search', $query);
        $this->assertRedirect('/search?filter=Placeholder+Gala&direction=future');
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
            ->where(['title' => 'Placeholder Gala'])
            ->firstOrFail();

        $this->session(['Auth.User.id' => 74]);

        $this->get("/events/delete/$event->id");
        $this->assertResponseSuccess();
    }
}
