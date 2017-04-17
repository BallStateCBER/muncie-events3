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
    public $helpers = ['Tag'];
    /* public $components = [
        'Calendar',
        'Search.Prg',
        'RequestHandler'
    ]; */
    public $uses = ['Event'];
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
        $this->loadComponent('Search.Prg', [
            'actions' => ['search']
        ]);
    }

    private function __isAdminOrAuthor($eventId)
    {
        if ($this->request->session()->read('Auth.User.role') == 'admin') {
            return true;
        }
        $userId = $this->request->session()->read('Auth.User.id');
        if ($userId) {
            $this->Events->id = $eventId;
            $authorId = $this->Events->field('user_id');
            if ($authorId) {
                return $userId == $authorId;
            }
        }
        return false;
    }

    public function isAuthorized()
    {
        // Admins can access everything
        if ($this->request->session()->read('Auth.User.role') == 'admin') {
            return true;

        // Some actions are admin-only
        } elseif (in_array($this->action, $this->adminActions)) {
            return false;
        }

        // Otherwise, only authors can modify authored content
        $authorOnly = ['edit', 'delete'];
        if (in_array($this->action, $authorOnly)) {
            return $this->__isAdminOrAuthor($this->request->params['named']['id']);
        }

        // Logged-in users can access everything else
        return true;
    }

    private function __prepareEventForm()
    {
        $userId = $this->request->session()->read('Auth.User.id');
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
        if (!$event['time_start']) {
            $event['time_start'] = '00:00:00';
        }
        if ($this->has['end_time'] && !$event['time_end']) {
            $event['time_end'] = '00:00:00';
        }

        // Fixes bug that prevents CakePHP from deleting all tags
        if (null !== $this->request->getData('Tags')) {
            $this->set('Tags', []);
        }

        // Collect more image data:
        // - Populate $this->request->data['Image'] with data about selected images
        // - Provide $images to the view with a list of all of this User's images
        $this->loadModel('Images');
        $images = $this->Events->Users->getImagesList($userId);
        if (! empty($this->request->data['EventsImage'])) {
            foreach ($this->request->data['EventsImage'] as $association) {
                $image_id = $association['image_id'];
                if (isset($images[$image_id])) {
                    $this->request->data['Images'][$image_id] = [
                        'id' => $image_id,
                        'filename' => $images[$image_id]
                    ];
                } else {
                    /* If an image is in $this->request->data['EventsImage']
                     * but not in the current user's images, then the user is
                     * probably an admin editing someone else's event. */
                    $this->Images->id = $image_id;
                    $filename = $this->Images->field('filename');
                    if ($filename) {
                        $images[$image_id] = $filename;
                        $this->request->data['Images'][$image_id] = [
                            'id' => $image_id,
                            'filename' => $images[$image_id]
                        ];
                    }
                }
            }
        }
        $this->set('images', $images);
    }

    private function __processImageData()
    {
        if (! isset($this->request->data['Images'])) {
            $this->request->data['Images'] = [];
        }
        if (empty($this->request->data['Images'])) {
            return;
        }
        $weight = 1;
        $this->request->data['EventsImages'] = [];
        foreach ($this->request->data['Images'] as $image_id => $caption) {
            $this->request->data['EventsImages'][] = [
                'image_id' => $image_id,
                'weight' => $weight,
                'caption' => $caption
            ];
            $weight++;
        }
        unset($this->request->data['Image']);
    }

    private function __prepareDatePicker()
    {
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
            list($year, $month, $day) = explode('-', $result->Events->date);
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
            // make sure the end time stays null if it needs to
            if (!$this->request->data['has_end_time']) {
                $this->request->data['time_end'] = null;
            }
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
        $this->__prepareDatePicker();
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
        if (empty($ids)) {
            $this->Flash->error('No events approved because no IDs were specified');
            $this->redirect('/');
        }
        #$seriesToApprove = [];
        foreach ($ids as $id) {
            $this->Events->id = $id;
            $event = $this->Events->get($id);
            if (!$this->Events->exists($id)) {
                $this->Flash->error('Cannot approve. Event with ID# '.$id.' not found.');
            }
                /*if ($seriesId = $this->Events->EventSeries->id) {
                    $seriesToApprove[$seriesId] = true;
                } */
                // approve & publish it
                $event['approved_by'] = $this->request->session()->read('Auth.User.id');
            $event['published'] = 1;

            $url = Router::url([
                    'controller' => 'events',
                    'action' => 'view',
                    'id' => $id
                ]);
            if ($this->Events->save($event)) {
                $this->Flash->success(__("Event #$id approved <a href=$url>Go to event page</a>"), ['escape' => false]);
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
        $identicalSeries = [];
        foreach ($unapproved as $k => $event) {
            if (empty($event['EventsSeries'])) {
                continue;
            }
            $eventId = $event['Events']['id'];
            $seriesId = $event['EventSeries']['id'];
            $modified = $event['Events']['modified'];
            if (isset($identicalSeries[$seriesId][$modified])) {
                unset($unapproved[$k]);
            }
            $identicalSeries[$seriesId][$modified][] = $eventId;
        }

        $this->set([
            'titleForLayout' => 'Review Unapproved Content',
            'unapproved' => $unapproved,
            'identicalSeries' => $identicalSeries
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
            $dates = explode(',', $this->request->event['date']);
            $tags = $this->request->event['tags'];
            #$isSeries = count($dates) > 1;
            $userId = $this->request->session()->read('Auth.User.id');

            // Correct date format
            foreach ($dates as &$date) {
                $date = trim($date);
                $timestamp = strtotime($date);
                $date = date('Y-m-d', $timestamp);
            }
            unset($date);

            // auto-approve if posted by an admin
            $this->request->data['user_id'] = $userId;
            if ($this->request->session()->read('Auth.User.role') == 'admin') {
                $this->request->data['approved_by'] = $this->request->session()->read('Auth.User.id');
            }

            // kill the end time if it hasn't been set
            if (!$this->has['end_time']) {
                $this->request->data['time_end'] = null;
            }

            $this->request->data['Tags'] = $tags;

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
        $this->set(compact('event', 'users', 'categories', /*'eventseries', */'images', 'tags'));
        $this->set('_serialize', ['event']);

        $this->__prepareEventForm();
        $this->__prepareDatePicker();
        $this->set('titleForLayout', 'Submit an Event');
    }

    public function search()
    {
        $filter= $this->request->query;

        // Determine the direction (past or future)
        $direction = $filter['direction'];

        $dateQuery = ($direction == 'future') ? 'date >=' : 'date <';
        if ($direction == 'all') {
            $dateQuery = 'date !=';
        };
        $dir = ($direction == 'future') ? 'ASC' : 'DESC';
        $dateWhen = ($direction == 'all') ? '1900-01-01' : date('Y-m-d');

        $events = $this->Events->find('search', [
            'search' => $filter,
            'contain' => ['Users', 'Categories', 'EventSeries', 'Images', 'Tags']])
            ->where([$dateQuery => $dateWhen])
            ->order(['date' => $dir])
            ->toArray();

        if ($events) {
            $this->indexEvents($events);
        }

        if ($direction == 'all') {
            $currentDate = date('Y-m-d');
            $counts = ['upcoming' => 0, 'past' => 0];
            foreach ($events as $date => $dateEvents) {
                if ($date >= $currentDate) {
                    $counts['upcoming']++;
                }
                if ($date < $currentDate) {
                    $counts['past']++;
                }
            }
            $this->set(compact('counts'));
        }
        if ($direction == 'past' || $direction = 'future') {
            // Determine if there are events in the opposite direction
            $this->passedArgs['direction'] = ($direction == 'future') ? 'past' : 'future';
            if ($this->passedArgs['direction'] == 'past') {
                $oppositeDirectionCount = $this->Events->find('search', [
                    'search' => $filter])
                    ->where(['date <' => date('Y-m-d')])
                    ->count();
            } elseif ($this->passedArgs['direction'] == 'future') {
                $oppositeDirectionCount = $this->Events->find('search', [
                    'search' => $filter])
                    ->where(['date >=' => date('Y-m-d')])
                    ->count();
            }
            $this->set('eventsFoundInOtherDirection', $oppositeDirectionCount);
        }

        $this->set([
            'titleForLayout' => 'Search Results',
            'direction' => $direction,
            'directionAdjective' => ($direction == 'future') ? 'upcoming' : $direction,
            'filter' => $filter,
            'dateQuery' => $dateQuery,
            'tags' => $this->Events->Tags->find('search', [
                'search' => $filter])
        ]);
    }
}
