<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\I18n\Date;
use Cake\I18n\Time;
use Cake\Routing\Router;

/**
 * Events Controller
 *
 * @property \App\Model\Table\EventsTable $Events
 */
class EventsController extends AppController
{
    public $name = 'Events';
    //public $helpers = ['Tag'];
    //public $components = [
    //    'Calendar',
    //    'Search.Prg',
    //    'RequestHandler'
    //];
    public $uses = ['Event'];
    public $paginate = [
        'order' => [
            'Event.date' => 'asc',
            'Event.time_start' => 'asc'
        ],
        'limit' => 15,
        'contain' => [
            'User' => [
                'fields' => ['id', 'name']
            ],
            'Category' => [
                'fields' => ['id', 'name', 'slug']
            ],
            'EventSeries' => [
                'fields' => ['id', 'title']
            ],
            'EventsImage' => [
                'fields' => ['id', 'caption'],
                'Image' => [
                    'fields' => ['id', 'filename']
                ]
            ],
            'Tag' => [
                'fields' => ['id', 'name']
            ]
        ]
    ];
    public $autoPublish = false; // false puts new additions into moderation queue
    public $eventFilter = [];
    public $adminActions = ['publish', 'approve', 'moderate'];

    public function initialize()
    {
        parent::initialize();
        // you don't need to log in to view events,
        // just to add & edit them
        $this->Auth->allow([
            'category', 'day', 'index', 'location', 'tag', 'view'
        ]);
    }

    private function __formatFormData()
    {
        $event = $this->request->getData('Event');

        if (!$event['time_start']) {
            // Fixes bug where midnight is saved as null
            $event['time_start'] = '00:00:00';
        }
        /* $event['description'] = strip_tags(
            $event['description'],
            $event['allowed_tags']
        ); */

        // Fixes bug that prevents CakePHP from deleting all tags
        if (null !== $this->request->getData('Tag')) {
            $this->set('Tag', []);
        }
    }

    private function __processCustomTags()
    {
        if (!isset($this->request->data['Events.custom_tags'])) {
            return;
        }
        $customTags = trim($this->request->data['Events.custom_tags']);
        if (empty($customTags)) {
            return;
        }
        $customTags = explode(',', $customTags);

        // Force lowercase and remove leading/trailing whitespace
        foreach ($customTags as &$ct) {
            $ct = strtolower(trim($ct));
        }
        unset($ct);

        // Remove duplicates
        $customTags = array_unique($customTags);

        $this->Events->Tags = $this->Events->Tags;
        foreach ($customTags as $ct) {
            // Skip over blank tags
            if ($ct == '') {
                continue;
            }

            // Get ID of existing tag, if it exists
            $tagId = $this->Events->Tags->field('id', ['name' => $ct]);

            // Include this tag if it exists and is selectable
            if ($tagId) {
                $selectable = $this->Events->Tags->field('selectable', ['id' => $tagId]);
                if ($selectable) {
                    $this->request->data['Tag'][] = $tagId;
                }
                if (!$selectable) {
                    continue;
                }

            // Create the custom tag if it does not already exist
            }
            if (!$tagId) {
                $this->Events->Tags->create();
                $this->Events->Tags->set([
                    'name' => $ct,
                    'user_id' => $this->Auth->user('id'),
                    'parent_id' => $this->Events->Tags->getUnlistedGroupId(), // 'Unlisted' group
                    'listed' => 0,
                    'selectable' => 1
                ]);
                $this->Events->Tags->save();
                $this->request->data['Tag'][] = $this->Events->Tags->id;
            }
        }
        $this->request->data['Tag'] = array_unique($this->request->data['Tag']);
        $this->request->data['Events.custom_tags'] = '';
    }

    private function __prepareEventForm()
    {
        $userId = $this->Auth->user('id');
        $this->set([
            'previous_locations' => $this->Events->getPastLocations(),
            'userId' => $userId
        ]);

        $event = $this->request->getData('Event');
        $available_tags = $this->Events->Tags->find('all', [
            'order' => ['parent_id' => 'ASC']
            ])
            ->toArray();
        $this->set([
            'available_tags' => $available_tags
        ]);

        if ($this->request->action == 'add' || $this->request->action == 'edit_series') {
            $hasSeries = count(explode(',', $event['date'])) > 1;
            $hasEndTime = isset($event['has_end_time']) ? $event['has_end_time'] : false;
        } elseif ($this->request->action == 'edit') {
            $hasSeries = isset($event['series_id']) ? (bool) $event['series_id'] : false;
            $hasEndTime = isset($event['time_end']) && $event['time_end'];
        }

        $this->set([
            'has' => [
                'series' => $hasSeries,
                'end_time' => $hasEndTime,
                'address' => isset($event['address']) && $event['address'],
                'cost' => isset($event['cost']) && $event['cost'],
                'ages' => isset($event['age_restriction']) && $event['age_restriction'],
                'source' => isset($event['source']) && $event['source']
            ]
        ]);

        // Fixes bug where midnight is saved as null
        if ($event['has_end_time']) {
            if (!$event['time_end']) {
                $event['time_end'] = '00:00:00';
            }
        }
        if (!$event['has_end_time']) {
            $event['time_end'] = null;
        }

        // Prepare date picker
        if ($this->request->action == 'add' || $this->request->action == 'edit_series') {
            $dateFieldValues = [];
            if (empty($event['date'])) {
                $defaultDate = 0; // Today
                $preselectedDates = '[]';
            } else {
                $dates = explode(',', $event['date']);
                foreach ($dates as $date) {
                    list($year, $month, $day) = explode('-', $date);
                    if (!isset($defaultDate)) {
                        $defaultDate = "$month/$day/$year";
                    }
                    $dateFieldValues[] = "$month/$day/$year";
                }
                $datesForJs = [];
                foreach ($dateFieldValues as $date) {
                    $datesForJs[] = "'".$date."'";
                }
                $datesForJs = implode(',', $datesForJs);
                $preselectedDates = "[$datesForJs]";
            }
            $this->set([
                'defaultDate' => $defaultDate,
                'preselectedDates' => $preselectedDates
            ]);
            $event['date'] = implode(',', $dateFieldValues);
        } elseif ($this->action == 'edit') {
            list($year, $month, $day) = explode('-', $event['date']);
            $event['date'] = "$month/$day/$year";
        }
    }

    public function datepickerPopulatedDates()
    {
        $results = $this->Events->getPopulatedDates();
        $dates = [];
        foreach ($results as $result) {
            list($year, $month, $day) = explode('-', $result->Event['date']);
            $dates["$month-$year"][] = $day;
        }
        $this->set(compact('dates'));
        $this->layout = 'blank';
    }

    public function edit($id = null)
    {
        $event = $this->Events->get($id, [
            'contain' => ['Images', 'Tags']
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $event = $this->Events->patchEntity($event, $this->request->getData());
            if ($this->Events->save($event)) {
                $this->Flash->success(__('The event has been saved.'));
            } else {
                $this->Flash->error(__('The event could not be saved. Please, try again.'));
            }
        }
        $users = $this->Events->Users->find('list');
        $categories = $this->Events->Categories->find('list');
        #$series = $this->Events->EventSeries->find('list');
        $images = $this->Events->Images->find('list');
        $tags = $this->Events->Tags->find('list');
        $this->set(compact('event', 'users', 'categories', /*'eventseries', */'images', 'tags'));
        $this->set('_serialize', ['event']);

        $this->__prepareEventForm();
        $this->set('titleForLayout', 'Edit Event');
    }

    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $event = $this->Events->get($id);
        if ($this->Events->delete($event)) {
            $this->Flash->success(__('The event has been deleted.'));
        } else {
            $this->Flash->error(__('The event could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    public function approve($id = null)
    {
        $ids = $this->request->pass;
        $adminId = $this->request->session()->read('Auth.User.id');
        if (empty($ids)) {
            $this->Flash->error('No events approved because no IDs were specified');
        } else {
            $seriesToApprove = [];
            foreach ($ids as $id) {
                $this->Events->id = $id;
                $event = $this->Events->get($id);
                if (!$this->Events->exists($id)) {
                    $this->Flash->error('Cannot approve. Event with ID# '.$id.' not found.');
                }
                /*if ($seriesId = $this->EventSeries->id) {
                    $seriesToApprove[$seriesId] = true;
                }*/
                $url = Router::url([
                    'controller' => 'events',
                    'action' => 'view',
                    'id' => $id
                ]);
                if ($this->Events->save($event)) {
                    $this->Flash->success("Event #$id approved.<a href=\"$url\">Go to event page</a>");
                }
            }
            foreach ($seriesToApprove as $seriesId => $flag) {
                $this->Events->EventSeries->id = $seriesId;
                $this->Events->EventSeries->save('published', true);
            }
        }
        $this->redirect($this->referer());
    }

    public function moderate()
    {
        // Collect all unapproved events
        $unapproved = $this->Events
            ->find('all', [
            'contain' => ['Users', 'Categories', 'EventSeries', 'Images', 'Tags'],
            'order' => ['date' => 'ASC']
            ])
            ->where(['approved_by IS' => null])
            ->toArray();

        // Find sets of identical events (belonging to the same series
        // and with the same modified date) and remove all but the first
        $identicalSeriesMembers = [];
        foreach ($unapproved as $k => $event) {
            if (empty($event['EventsSeries'])) {
                continue;
            }
            $event_id = $event['Events']['id'];
            $seriesId = $event['EventSeries']['id'];
            $modified = $event['Events']['modified'];
            if (isset($identicalSeriesMembers[$seriesId][$modified])) {
                unset($unapproved[$k]);
            }
            $identicalSeriesMembers[$seriesId][$modified][] = $event_id;
        }

        $this->set([
            'titleForLayout' => 'Review Unapproved Content',
            'unapproved' => $unapproved,
            'identicalSeriesMembers' => $identicalSeriesMembers
        ]);
    }


    // home page
    public function index()
    {
        $events = $this->Events
            ->find('all', [
            'contain' => ['Users', 'Categories', 'EventSeries', 'Images', 'Tags'],
            'order' => ['date' => 'ASC']
            ])
            ->where(['date >=' => date('Y-m-d')])
            ->toArray();
        $this->indexEvents($events);
        $this->set('titleForLayout', '');
    }

    public function tag($slug = '')
    {
        // Get tag
        $tagId = $this->Events->Tags->getIdFromSlug($slug);
        $tag = $this->Events->Tags->find('all', [
            'conditions' => ['id' => $tagId],
            'fields' => ['id', 'name'],
            'contain' => false
        ])->first();
        if (empty($tag)) {
            return $this->renderMessage([
                'title' => 'Tag Not Found',
                'message' => "Sorry, but we couldn't find that tag ($slug)",
                'class' => 'error'
            ]);
        }

        $eventId = $this->Events->getIdsFromTag($tagId);
        $events = $this->Events
            ->find('all', [
                'contain' => ['Users', 'Categories', 'EventSeries', 'Images', 'Tags'],
                'order' => ['date' => 'DESC']
            ])
            ->where(['Events.id IN' => $eventId])
            ->toArray();
        $this->indexEvents($events);

        $this->set([
            'titleForLayout' => 'Tag: '.ucwords($tag->name),
            'eventId' => $eventId,
            'tag' => $tag,
            'slug' => $slug
        ]);
    }

    public function location($location = null)
    {
        $events = $this->Events
            ->find('all', [
            'conditions' => ['location' => $location],
            'contain' => ['Users', 'Categories', 'EventSeries', 'Images', 'Tags'],
            'order' => ['date' => 'DESC']
            ])
            ->toArray();
        $this->indexEvents($events);
        $this->set('location', $location);
        $this->set('titleForLayout', '');
    }

    public function pastLocations()
    {
        $locations = $this->Events->getPastLocations();
        $this->set([
            'titleForLayout' => 'Locations of Past Events',
            'pastLocations' => $locations,
            'listPastLocations' => true
        ]);
    }

    public function getFilteredEventsOnDates($date)
    {
        $events = $this->Events
            ->find('all', [
            'conditions' => ['date' => $date],
            'contain' => ['Users', 'Categories', 'EventSeries', 'Images', 'Tags'],
            'order' => ['date' => 'DESC']
            ])
            ->toArray();
        $this->indexEvents($events);
    }

    public function view($id = null)
    {
        $event = $this->Events->get($id, [
            'contain' => ['Users', 'Categories', 'EventSeries', 'Images', 'Tags']
        ]);

        $this->set('event', $event);
        $this->set('_serialize', ['event']);
        $this->set('titleForLayout', $event['title']);
    }

    public function category($slug)
    {
        $category = $this->Events->Categories->find('all', [
            'conditions' => ['slug' => $slug]
            ])
            ->first();
        $events = $this->Events->find('all', [
            'conditions' => ['category_id' => $category->id],
            'contain' => ['Users', 'Categories', 'EventSeries', 'Images', 'Tags'],
            'order' => ['date' => 'DESC']
            ])
            ->toArray();
        if (empty($events)) {
            return $this->renderMessage([
                'title' => 'Category Not Found',
                'message' => "Sorry, but we couldn't find the category \"$slug\".",
                'class' => 'error'
            ]);
        }
        $this->indexEvents($events);
        $this->set([
            'category' => $category,
            'titleForLayout' => $category->name
        ]);
    }

    public function day($month = null, $day = null, $year = null)
    {
        if (! $year || ! $month || ! $day) {
            $this->redirect('/');
        }

        // Zero-pad day and month numbers
        $month = str_pad($month, 2, '0', STR_PAD_LEFT);
        $day = str_pad($day, 2, '0', STR_PAD_LEFT);
        $date = "$year-$month-$day";
        $events = $this->Events
            ->find('all', [
            'conditions' => ['date' => $date],
            'contain' => ['Users', 'Categories', 'EventSeries', 'Images', 'Tags'],
            'order' => ['date' => 'DESC']
            ])
            ->toArray();
        if ($events) {
            $this->indexEvents($events);
        }
        $timestamp = mktime(0, 0, 0, $month, $day, $year);
        $dateString = date('F j, Y', $timestamp);
        $this->set(compact('month', 'year', 'day'));
        $this->set([
            'titleForLayout' => 'Events on '.$dateString,
            'displayedDate' => date('l F j, Y', $timestamp)
        ]);
    }

    public function add()
    {
        $event = $this->Events->newEntity();

        if ($this->request->is(['patch', 'post', 'put'])) {
            //$dates = explode(',', $this->request->Event['date']);
            //$is_series = count($dates) > 1;

            // process data
            $this->__formatFormData();
            $this->__processCustomTags();

            // Correct date format
            /*foreach ($dates as &$date) {
                $date = trim($date);
                $timestamp = strtotime($date);
                $date = date('Y-m-d', $timestamp);
            }
            unset($date);*/

            $event = $this->Events->patchEntity($event, $this->request->getData());
            if ($this->Events->save($event)) {
                $this->Flash->success(__('The event has been saved.'));
            } else {
                $this->Flash->error(__('The event could not be saved. Please, try again.'));
            }
        }
        $users = $this->Events->Users->find('list');
        $categories = $this->Events->Categories->find('list');
        $images = $this->Events->Images->find('list');
        $tags = $this->Events->Tags->find('list');
        $this->set(compact('event', 'users', 'categories', 'eventseries', 'images', 'tags'));
        $this->set('_serialize', ['event']);

        $this->__prepareEventForm();
        $this->set('titleForLayout', 'Submit an Event');
    }
}
