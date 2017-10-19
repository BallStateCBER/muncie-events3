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
    public $helpers = ['Tag', 'Calendar'];
    public $components = [
        'Search.Prg',
        'RequestHandler'
    ];
    public $uses = ['Event'];
    public $eventFilter = [];

    /**
     * Initialization hook method.
     *
     * @return void
     */
    public function initialize()
    {
        parent::initialize();
        // you don't need to log in to view events,
        // just to add & edit them
        $this->Auth->allow([
            'add', 'category', 'datepickerPopulatedDates', 'day', 'ics', 'index', 'location', 'month', 'search', 'searchAutocomplete', 'tag', 'today', 'tomorrow', 'view'
        ]);
        $this->loadComponent('Search.Prg', [
            'actions' => ['search']
        ]);
    }

    /**
     * determine if admin
     *
     * @param ResultSet $event need to check
     * @return redirect
     */
    private function isAdmin($event = null)
    {
        if ($this->request->session()->read('Auth.User.role') != 'admin') {
            if (isset($event)) {
                if ($this->request->action == 'delete' && $event->user_id != $this->request->session()->read('Auth.User.id')) {
                    $this->Flash->error(__('You are not authorized to view this page.'));

                    return $this->redirect('/');
                }

                if ($this->request->action == 'edit' && $event->user_id != $this->request->session()->read('Auth.User.id')) {
                    $this->Flash->error(__('You are not authorized to view this page.'));

                    return $this->redirect('/');
                }

                if ($this->request->action == 'editseries' && $event->user_id != $this->request->session()->read('Auth.User.id')) {
                    $this->Flash->error(__('You are not authorized to view this page.'));

                    return $this->redirect('/');
                }
            }

            $adminActions = ['approve', 'moderate'];
            foreach ($adminActions as $action) {
                if ($this->request->action == $action) {
                    $this->Flash->error(__('You are not authorized to view this page.'));

                    return $this->redirect('/');
                }
            }
        }
    }

    /**
     * setCustomTags method
     *
     * @param ResultSet $event Event entity
     * @return void
     */
    private function setCustomTags($event)
    {
        if (!isset($event->customTags)) {
            return;
        }
        $customTags = trim($event->customTags);
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

        foreach ($customTags as $ct) {
            // Skip over blank tags
            if ($ct == '') {
                continue;
            }

            // Get ID of existing tag, if it exists
            $tagId = $this->Events->Tags->find()
                     ->select('id')
                     ->where(['name' => $ct])
                     ->first();

            // Include this tag if it exists and is selectable
            if ($tagId) {
                $selectable = $this->Events->Tags->find()
                              ->select('selectable')
                              ->where(['id' => $tagId->id])
                              ->toArray();
                if (!$selectable) {
                    continue;
                }
                $this->request->data['data']['Tags'][] = $tagId;
            }
            // Create the custom tag if it does not already exist
            if (!$tagId) {
                $newTag = $this->Events->Tags->newEntity();
                $newTag->name = $ct;
                $newTag->user_id = $this->Auth->user('id');
                $newTag->parent_id = 1012; // 'Unlisted' group
                $newTag->listed = $this->Auth->user('role') == 'admin' ? 1 : 0;
                $newTag->selectable = 1;

                $this->Events->Tags->save($newTag);
                $this->request->data['data']['Tags'][] = $newTag->id;
            }
        }
        $this->request->data['data']['Tags'] = array_unique($this->request->data['data']['Tags']);
        $event->customTags = '';
    }

    /**
     * setDatePicker method
     *
     * @param ResultSet $event Event entity
     * @return void
     */
    private function setDatePicker($event)
    {
        // Prepare date picker
        if ($this->request->action == 'add') {
            $dateFieldValues = [];
            $preselectedDates = '[]';
            $defaultDate = 0; // Today
        }
        if ($this->request->action == 'editseries') {
            $dateFieldValues = [];
            foreach ($event->date as $date) {
                list($year, $month, $day) = explode('-', $date);
                if (!isset($defaultDate)) {
                    $defaultDate = "$month/$day/$year";
                }
                $dateFieldValues[] = date_create("$month/$day/$year");
            }
            $preselectedDates = [];
            $eventSelectedDates = [];
            foreach ($dateFieldValues as $date) {
                $preselectedDates[] = "'" . date_format($date, 'm/d/Y') . "'";
                $eventSelectedDates[] = date_format($date, 'm/d/Y');
            }
            $preselectedDates = implode(',', $preselectedDates);
            $eventSelectedDates = implode(',', $eventSelectedDates);
            $event->date = $eventSelectedDates;
            $preselectedDates = '[' . $preselectedDates . ']';
        }
        $this->set(compact('dateFieldValues', 'defaultDate', 'preselectedDates'));
    }

    /**
     * setEventForm method
     *
     * @param ResultSet $event Event entity
     * @return void
     */
    private function setEventForm($event)
    {
        $userId = $this->request->session()->read('Auth.User.id');
        $this->set([
            'previousLocations' => $this->Events->getPastLocations(),
            'userId' => $userId,
        ]);

        // prepare the tag helper
        $availableTags = $this->Events->Tags->find()
            ->where(['listed' => 1])
            ->order(['name' => 'ASC'])
            ->toArray();
        $this->set(compact('availableTags'));

        if ($this->request->action == 'add' || $this->request->action == 'editseries') {
            $hasSeries = count($event->date) > 1;
            $hasEndTime = isset($event['time_end']);
        } elseif ($this->request->action == 'edit') {
            $hasSeries = isset($event['series_id']);
            $hasEndTime = isset($event['time_end']) && $event['time_end'];
        }
        if (!isset($hasSeries)) {
            $hasSeries = false;
        }
        if (!isset($hasEndTime)) {
            $hasEndTime = false;
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
    }

    /**
     * setImageData method
     *
     * @param ResultSet $event Event entity
     * @return void
     */
    private function setImageData($event)
    {
        $weight = 1;
        $place = 0;
        $imageData = isset($this->request->data['newImages']) ? $this->request->data['newImages'] : null;
        if ($imageData) {
            foreach ($imageData as $imageId => $caption) {
                $newImage = $this->Events->Images->get($imageId);
                $delete = $this->request->data['delete'][$imageId];
                if ($delete == 1) {
                    $this->Events->Images->unlink($event, [$newImage]);
                }
                if ($delete == 0) {
                    $event->images[$place]->_joinData->weight = $weight;
                    $event->images[$place]->_joinData->caption = $caption;
                    $event->images[$place]->_joinData->created = $newImage->created;
                    $event->images[$place]->_joinData->modified = $newImage->modified;
                }

                $weight++;
                $place++;
            }
        }
        $imageData = isset($this->request->data['data']['Image']) ? $this->request->data['data']['Image'] : null;
        if ($imageData) {
            foreach ($imageData as $imageId => $caption) {
                $newImage = $this->Events->Images->get($imageId);

                $newImage->_joinData = $this->Events->EventsImages->newEntity();
                $newImage->_joinData->weight = $weight;
                $newImage->_joinData->caption = $caption;
                $newImage->_joinData->created = $newImage->created;
                $newImage->_joinData->modified = $newImage->modified;

                $this->Events->Images->link($event, [$newImage]);

                $weight++;
                $place++;
            }
        }
        $event->dirty('images', true);
    }

    /**
     * approve method
     *
     * @param int|null $id Event entity id
     * @return void
     */
    public function approve($id = null)
    {
        $this->isAdmin();
        $ids = $this->request->pass;
        if (empty($ids)) {
            $this->Flash->error('No events approved because no IDs were specified');
            $this->redirect('/');
        }
        $seriesToApprove = [];
        foreach ($ids as $id) {
            $this->Events->id = $id;
            $event = $this->Events->get($id);
            if (!$this->Events->exists($id)) {
                $this->Flash->error('Cannot approve. Event with ID# ' . $id . ' not found.');
            }
            if ($event['event_series']['id']) {
                $seriesId = $event['event_series']['id'];
                $seriesToApprove[$seriesId] = true;
            }
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

    /**
     * add method
     *
     * @return redirect
     */
    public function add()
    {
        $event = $this->Events->newEntity();

        // prepare form
        $this->setEventForm($event);
        $this->setImageData($event);
        $this->setDatePicker($event);

        $users = $this->Events->Users->find('list');
        $categories = $this->Events->Categories->find('list');
        $eventseries = $this->Events->EventSeries->find('list');
        $this->set(compact('event', 'users', 'categories', 'eventseries'));
        $this->set('_serialize', ['event']);
        $this->set('titleForLayout', 'Submit an Event');

        if ($this->request->is(['patch', 'post', 'put'])) {
            $this->uponFormSubmissionPr();

            // count how many dates have been picked
            $dateInput = strlen($this->request->data['date']);

            // a single event
            if ($dateInput == 10) {
                $event = $this->Events->patchEntity($event, $this->request->getData());
                $this->setCustomTags($event);
                $event->date = new Date($this->request->data['date']);
                $event->series_id = null;
                if ($this->Events->save($event, [
                    'associated' => ['Images', 'Tags']
                ])) {
                    return $this->Flash->success(__('The event has been saved.'));
                }
            }

            // a series of multiple events
            if ($dateInput > 10) {
                // save the series itself
                $eventSeries = $this->Events->EventSeries->newEntity();
                $eventSeries = $this->Events->EventSeries->patchEntity($eventSeries, $this->request->getData());
                $eventSeries->title = $this->request->data['title'];
                $eventSeries->user_id = $this->request->session()->read('Auth.User.id');
                $eventSeries->published = ($this->request->session()->read('Auth.User.role') == 'admin') ? 1 : 0;
                $eventSeries->created = date('Y-m-d');
                $eventSeries->modified = date('Y-m-d');
                $this->Events->EventSeries->save($eventSeries);

                // now save every event
                $dates = explode(',', $this->request->data['date']);
                foreach ($dates as $date) {
                    $newDate = new Date($date);
                    $event = $this->Events->newEntity();
                    $event = $this->Events->patchEntity($event, $this->request->getData());
                    $this->setCustomTags($event);
                    $event->date = $newDate;
                    $event->series_id = $eventSeries->id;
                    $this->Events->save($event, [
                        'associated' => ['EventSeries', 'Images', 'Tags']
                    ]);
                }

                return $this->Flash->success(__('The event series has been saved.'));
            }

            // if neither a single event nor multiple-event series can be saved
            if (!$this->Events->save($event)) {
                return $this->Flash->error(__('The event could not be saved. Please, try again.'));
            }
        }
    }

    /**
     * category method
     *
     * @param string $slug Category entity slug
     * @param string|null $nextStartDate param for Events
     * @return void
     */
    public function category($slug, $nextStartDate = null)
    {
        if ($nextStartDate == null) {
            $nextStartDate = date('Y-m-d');
        }
        $category = $this->Events->Categories->find('all', [
            'conditions' => ['slug' => $slug]
            ])
            ->first();
        // the app breaks if there is a 2-week gap in between events
        $endDate = strtotime($nextStartDate . ' + 2 weeks');
        $events = $this->Events
            ->find('all', [
            'contain' => ['Users', 'Categories', 'EventSeries', 'Images', 'Tags'],
            'order' => ['date' => 'ASC']
            ])
            ->where(['date >=' => $nextStartDate])
            ->andWhere(['category_id' => $category->id])
            ->andwhere(['date <=' => $endDate])
            ->toArray();
        if ($events) {
            $this->indexEvents($events);
        }
        $this->set([
            'category' => $category,
            'titleForLayout' => $category->name
        ]);
    }

    /**
     * Produces a view with JS used by the datepicker in the header
     *
     * @return void
     */
    public function datepickerPopulatedDates()
    {
        $results = $this->Events->getPopulatedDates();
        $dates = [];
        foreach ($results as $result) {
            list($year, $month, $day) = explode('-', $result);
            $dates["$month-$year"][] = $day;
        }
        $this->set(compact('dates'));
        #$this->viewBuilder()->setLayout('blank');
    }

    /**
     * day method
     *
     * @param string|null $month param for Events
     * @param string|null $day param for Events
     * @param string|null $year param for Events
     * @return void
     */
    public function day($month = null, $day = null, $year = null)
    {
        if (! $year || ! $month || ! $day) {
            $this->redirect('/');
        }

        // Zero-pad day and month numbers
        $month = str_pad($month, 2, '0', STR_PAD_LEFT);
        $day = str_pad($day, 2, '0', STR_PAD_LEFT);
        $events = $this->Events->getEventsOnDay($year, $month, $day);
        if ($events) {
            $this->indexEvents($events);
        }
        $timestamp = mktime(0, 0, 0, $month, $day, $year);
        $dateString = date('F j, Y', $timestamp);
        $this->set(compact('month', 'year', 'day'));
        $this->set([
            'titleForLayout' => 'Events on ' . $dateString,
            'displayedDate' => date('l F j, Y', $timestamp)
        ]);
    }

    /**
     * delete method
     *
     * @param int|null $id id for series
     * @return redirect
     */
    public function delete($id = null)
    {
        $event = $this->Events->get($id);
        $this->isAdmin();
        if ($this->Events->delete($event)) {
            $this->Flash->success(__('The event has been deleted.'));

            return $this->redirect('/');
        }
        $this->Flash->error(__('The event could not be deleted. Please, try again.'));

        return $this->redirect(['action' => 'index']);
    }

    /**
     * edit method
     *
     * @param int $id id for series
     * @return redirect
     */
    public function edit($id = null)
    {
        $event = $this->Events->get($id, [
            'contain' => ['EventSeries', 'Images', 'Tags']
        ]);

        $this->isAdmin($event);
        // prepare form
        $this->setEventForm($event);
        $this->setImageData($event);
        $this->setDatePicker($event);

        $users = $this->Events->Users->find('list');
        $categories = $this->Events->Categories->find('list');
        $eventseries = $this->Events->EventSeries->find('list');
        $this->set(compact('event', 'users', 'categories', 'eventseries'));
        $this->set('_serialize', ['event']);
        $this->set('titleForLayout', 'Edit Event');

        if ($this->request->is(['patch', 'post', 'put'])) {
            // make sure the end time stays null if it needs to
            $this->uponFormSubmissionPr();
            $event = $this->Events->patchEntity($event, $this->request->getData());
            $event->date = strtotime($this->request->data['date']);
            $this->setCustomTags($event);
            if ($this->Events->save($event, [
                'associated' => ['EventSeries', 'Images', 'Tags']
            ])) {
                $event->date = $this->request->data['date'];

                return $this->Flash->success(__('The event has been saved.'));
            }
            return $this->Flash->error(__('The event could not be saved. Please, try again.'));
        }
    }

    /**
     * editseries method
     *
     * @param int $seriesId id for series
     * @return Cake\View\Helper\FlashHelper
     */
    public function editseries($seriesId)
    {
        // Get information about series
        $eventSeries = $this->Events->EventSeries->get($seriesId);
        if (!$eventSeries) {
            return $this->Flash->error('Sorry, it looks like you were trying to edit an event series that doesn\'t exist anymore.');
        }
        $events = $this->Events->find('all', [
            'conditions' => ['series_id' => $seriesId],
            'contain' => ['EventSeries']
            ])
            ->order(['date' => 'ASC'])
            ->toArray();

        $dates = [];
        foreach ($events as $event) {
            $dateString = date_format($event->date, 'Y-m-d');
            $dates[] = $dateString;
        }

        // Pick the first event in the series
        $eventId = $events[0]->id;
        $event = $this->Events->get($eventId, [
            'contain' => ['EventSeries']
        ]);

        $this->isAdmin($event);

        $event->date = $dates;
        $this->setEventForm($event);
        $this->setDatePicker($event);

        $this->Flash->error('Warning: all events in this series will be overwritten.');

        $categories = $this->Events->Categories->find('list');
        $this->set([
            'titleForLayout' => 'Edit Event Series: ' . $eventSeries['title']
        ]);
        $this->set(compact('categories', 'dates', 'event', 'events', 'eventSeries'));
        $this->render('/Element/events/form');

        if ($this->request->is('put') || $this->request->is('post')) {
            // save every event
            $newDates = explode(',', $this->request->data['date']);
            foreach ($dates as $date) {
                $oldDate = date('m/d/Y', strtotime($date));
                if (!in_array($oldDate, $newDates)) {
                    $deleteEvent = $this->Events->getEventsByDateAndSeries($date, $seriesId);
                    if ($this->Events->delete($deleteEvent)) {
                        $this->Flash->success(__("Event '$deleteEvent->title' has been deleted."));
                    }
                }
            }
            foreach ($newDates as $date) {
                $date = date('Y-m-d', strtotime($date));
                $oldEvent = $this->Events->getEventsByDateAndSeries($date, $seriesId);
                if (isset($oldEvent->id)) {
                    $event = $this->Events->get($oldEvent->id);
                }
                if (!isset($oldEvent->id)) {
                    $event = $this->Events->newEntity();
                }

                $event->category_id = $this->request->data['category_id'];
                $newDate = new Date($date);
                $event->date = $newDate;
                $event->description = $this->request->data['description'];
                $event->location = $this->request->data['location'];
                $optional = ['time_end', 'age_restriction', 'cost', 'source', 'address', 'location_details'];
                foreach ($optional as $option) {
                    if (isset($this->request->data[$option])) {
                        if ($option = 'time_end') {
                            $time = $this->request->data['time_end'];
                            $time = date('H:i:s', strtotime($time['hour'] . ':' . $time['minute'] . ' ' . $time['meridian']));
                            $event->time_end = new Time($time);
                            continue;
                        }
                        $event->$option = $this->request->data[$option];
                    }
                }
                $event->series_id = $seriesId;
                $time = $this->request->data['time_start'];
                $time = date('H:i:s', strtotime($time['hour'] . ':' . $time['minute'] . ' ' . $time['meridian']));
                $event->time_start = new Time($time);
                $event->title = $this->request->data['title'];

                $this->setCustomTags($event);
                if ($this->Events->save($event, [
                    'associated' => ['EventSeries', 'Images', 'Tags']
                ])) {
                    $this->Flash->success(__("Event '$event->title' has been saved."));
                    continue;
                }
                if (!$this->Events->save($event)) {
                    $this->Flash->error(__("The event '$event->title' (#$event->id) could not be saved."));
                }
            }
            $series = $this->Events->EventSeries->get($seriesId);
            $series = $this->Events->EventSeries->patchEntity($series, $this->request->getData());
            $series->title = $this->request->data['event_series']['title'];
            if ($this->Events->EventSeries->save($series)) {
                return $this->Flash->success(__("The event series '$series->title' was saved."));
            }
            if (!$this->Events->EventSeries->save($series)) {
                return $this->Flash->error(__("The event series '$series->title' was not saved."));
            }
        }
    }

    /**
     * getAddress method
     *
     * @param  string $location we need address
     * @return void
     */
    public function getAddress($location = '')
    {
        $this->viewBuilder()->setLayout('blank');
        $this->set('address', $this->Events->getAddress($location));
    }

    /**
     * getFilteredEventsOnDates method
     *
     * @param string $date date object
     * @return void
     */
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

    /**
     * ics method
     *
     * @return \Cake\Controller\Controller::render('/Events/ics/view')
     */
    public function ics()
    {
        $this->response->type('text/calendar');
        $this->response->download('foo.bar');
        $this->viewBuilder()->setLayout('ics/default');

        return $this->render('/Events/ics/view');
    }

    /**
     * index method
     *
     * @param string|null $nextStartDate next start date for Event entity
     * @return void
     */
    public function index($nextStartDate = null)
    {
        if ($nextStartDate == null) {
            $nextStartDate = date('Y-m-d');
        }
        $endDate = strtotime($nextStartDate . ' + 2 weeks');
        $events = $this->Events->getStartEndEvents($nextStartDate, $endDate, null);
        $this->indexEvents($events);
    }

    /**
     * location method
     *
     * @param string|null $location location of Event entity
     * @param string|null $direction of index
     * @return void
     */
    public function location($location = null, $direction = null)
    {
        $dir = $direction == 'past' ? 'ASC' : 'DESC';
        $date = $direction == 'past' ? '<' : '>=';
        $oppDir = $direction == 'past' ? 'DESC' : 'ASC';
        $oppDate = $direction == 'past' ? '>=' : '<';
        $opposite = $direction == 'past' ? 'upcoming' : 'past';

        $listing = $this->Events
            ->find('all', [
            'conditions' => [
                'location' => $location,
                "date $date" => date('Y-m-d')
            ],
            'contain' => ['Users', 'Categories', 'EventSeries', 'Images', 'Tags'],
            'order' => ['date' => $dir]
            ]);
        $listing = $this->paginate($listing)->toArray();
        $this->indexEvents($listing);

        $count = $this->Events
            ->find('all', [
                'contain' => ['Users', 'Categories', 'EventSeries', 'Images', 'Tags'],
                'order' => ['date' => $dir]
            ])
            ->where(['location' => $location])
            ->andWhere(["Events.date $date" => date('Y-m-d')])
            ->count();

        $oppCount = $this->Events
            ->find('all', [
                'contain' => ['Users', 'Categories', 'EventSeries', 'Images', 'Tags'],
                'order' => ['date' => $oppDir]
            ])
            ->where(['location' => $location])
            ->andWhere(["Events.date $oppDate" => date('Y-m-d')])
            ->count();

        $this->set(compact('count', 'direction', 'location', 'oppCount', 'opposite'));
        $this->set('multipleDates', true);
        $this->set(['slug' => $location]);
        $this->set('titleForLayout', '');
    }

    /**
     * moderate method
     *
     * @return void
     */
    public function moderate()
    {
        $this->isAdmin();
        // Collect all unapproved events
        $unapproved = $this->Events
            ->find('all', [
            'contain' => ['Users', 'Categories', 'EventSeries', 'Images', 'Tags'],
            'order' => ['date' => 'ASC']
            ])
            ->where(['Events.approved_by' => null])
            ->orWhere(['Events.published' => '0'])
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

    /**
     * month method
     *
     * @param string|null $month month of Event
     * @param string|null $year year of Event
     * @return void
     */
    public function month($month = null, $year = null)
    {
        if (!$month || !$year) {
            $this->redirect('/');
        }

        // Zero-pad day and month numbers
        $month = str_pad($month, 2, '0', STR_PAD_LEFT);
        $events = $this->Events
            ->find('all', [
            'conditions' => [
                'MONTH(date)' => $month,
                'YEAR(date)' => $year
                ],
            'contain' => ['Users', 'Categories', 'EventSeries', 'Images', 'Tags'],
            'order' => ['date' => 'asc']
            ])
            ->toArray();
        if ($events) {
            $this->indexEvents($events);
        }
        $timestamp = mktime(0, 0, 0, $month, 01, $year);
        $dateString = date('F, Y', $timestamp);
        $this->set(compact('month', 'year'));
        $this->set([
            'titleForLayout' => 'Events in ' . $dateString,
            'displayedDate' => date('F, Y', $timestamp)
        ]);
    }

    /**
     * pastLocations method
     *
     * @return void
     */
    public function pastLocations()
    {
        $locations = $this->Events->getPastLocations();
        $this->set([
            'titleForLayout' => 'Locations of Past Events',
            'pastLocations' => $locations,
            'listPastLocations' => true
        ]);
    }

    /**
     * search method
     *
     * @return void
     */
    public function search()
    {
        $filter = $this->request->query;

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
            ->order(['date' => $dir]);

        $events = $this->paginate($events)->toArray();

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
                $oppositeCount = $this->Events->find('search', [
                    'search' => $filter])
                    ->where(['date <' => date('Y-m-d')])
                    ->count();
            } elseif ($this->passedArgs['direction'] == 'future') {
                $oppositeCount = $this->Events->find('search', [
                    'search' => $filter])
                    ->where(['date >=' => date('Y-m-d')])
                    ->count();
            }
            $this->set('oppositeEvents', $oppositeCount);
        }

        $tags = $this->Events->Tags->find('search', [
            'search' => $filter]);
        $tagCount = null;
        foreach ($tags as $tag) {
            if ($tag->id) {
                $tagCount = true;
            }
        }

        $this->set([
            'titleForLayout' => 'Search Results',
            'direction' => $direction,
            'directionAdjective' => ($direction == 'future') ? 'upcoming' : $direction,
            'filter' => $filter,
            'dateQuery' => $dateQuery,
            'tags' => $tags,
            'tagCount' => $tagCount
        ]);
    }

    /**
     * searchAutocomplete method
     *
     * @return void
     */
    public function searchAutocomplete()
    {
        $stringToComplete = filter_input(INPUT_GET, 'term');
        $limit = 10;

        // name will be compared via LIKE to each of these,
        // in order, until $limit tags are found.
        $likeConditions = [
            $stringToComplete,
            $stringToComplete . ' %',
            $stringToComplete . '%',
            '% ' . $stringToComplete . '%',
            '%' . $stringToComplete . '%'
        ];

        // Collect tags up to $limit
        $tags = [];
        foreach ($likeConditions as $like) {
            if (count($tags) == $limit) {
                break;
            }
            $newLimit = $limit - count($tags);
            $results = $this->Events->tags->find()
                ->limit($newLimit)
                ->where(['name LIKE' => $like])
                ->andWhere(['listed' => 1])
                ->andWhere(['selectable' => 1])
                ->select(['id', 'name'])
                ->contain(false)
                ->toArray();

            if (!empty($results)) {
                foreach ($results as $result) {
                    if (!in_array($result->name, $tags)) {
                        $tagId = $result->id;
                        $tags[$tagId] = $result->name;
                    }
                }
            }
        }

        $x = 0;
        foreach ($tags as $tag) {
            $this->set([
                $x => $tag
            ]);
            $x = $x + 1;
        }
        $this->viewBuilder()->setLayout('ajax');
    }

    /**
     * tag method
     *
     * @param string|null $slug tag slug
     * @param string|null $direction of results
     * @return Cake\View\Helper\FlashHelper
     */
    public function tag($slug = '', $direction = null)
    {
        $dir = $direction == 'past' ? 'ASC' : 'DESC';
        $date = $direction == 'past' ? '<' : '>=';
        $oppDir = $direction == 'past' ? 'DESC' : 'ASC';
        $oppDate = $direction == 'past' ? '>=' : '<';
        $opposite = $direction == 'past' ? 'upcoming' : 'past';

        // Get tag
        $tagId = $this->Events->Tags->getIdFromSlug($slug);
        $tag = $this->Events->Tags->find('all', [
            'conditions' => ['id' => $tagId],
            'fields' => ['id', 'name'],
            'contain' => false
        ])->first();
        if (empty($tag)) {
            return $this->Flash->error("Sorry, but we couldn't find that tag ($slug)");
        }

        $eventId = $this->Events->getIdsFromTag($tagId);
        $listing = $this->Events
            ->find('all', [
                'contain' => ['Users', 'Categories', 'EventSeries', 'Images', 'Tags'],
                'order' => ['date' => $dir]
            ])
            ->where(['Events.id IN' => $eventId])
            ->andWhere(["Events.date $date" => date('Y-m-d')]);
        $listing = $this->paginate($listing)->toArray();
        $this->indexEvents($listing);

        $count = $this->Events
            ->find('all', [
                'contain' => ['Users', 'Categories', 'EventSeries', 'Images', 'Tags'],
                'order' => ['date' => $dir]
            ])
            ->where(['Events.id IN' => $eventId])
            ->andWhere(["Events.date $date" => date('Y-m-d')])
            ->count();

        $oppCount = $this->Events
            ->find('all', [
                'contain' => ['Users', 'Categories', 'EventSeries', 'Images', 'Tags'],
                'order' => ['date' => $oppDir]
            ])
            ->where(['Events.id IN' => $eventId])
            ->andWhere(["Events.date $oppDate" => date('Y-m-d')])
            ->count();

        $this->set(compact('count', 'direction', 'eventId', 'oppCount', 'opposite', 'slug', 'tag'));
        $this->set([
            'titleForLayout' => 'Tag: ' . ucwords($tag->name)
        ]);
    }

    /**
     * today method
     *
     * @return void
     */
    public function today()
    {
        $this->redirect('/events/day/' . date('m') . '/' . date('d') . '/' . date('Y'));
    }

    /**
     * tomorrow method
     *
     * @return void
     */
    public function tomorrow()
    {
        $tomorrow = date('m-d-Y', strtotime('+1 day'));
        $tomorrow = explode('-', $tomorrow);
        $this->redirect('/events/day/' . $tomorrow[0] . '/' . $tomorrow[1] . '/' . $tomorrow[2]);
    }

    /**
     * uponFormSubmissionPr method
     *
     * @return void
     */
    private function uponFormSubmissionPr()
    {
        // kill the end time if it hasn't been set
        if (!isset($this->request->data['has_end_time'])) {
            $this->request->data['time_end'] = null;
        }

        // auto-approve if posted by an admin
        $userId = $this->request->session()->read('Auth.User.id') ?: null;
        $this->request->data['user_id'] = $userId;
        if ($this->request->session()->read('Auth.User.role') == 'admin') {
            $this->request->data['approved_by'] = $userId;
            $this->request->data['published'] = true;
        }
    }

    /**
     * View method
     *
     * @param string|null $id Event id.
     * @return void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $event = $this->Events->get($id, [
            'contain' => ['Users', 'Categories', 'EventSeries', 'Images', 'Tags']
        ]);

        $this->set('event', $event);
        $this->set('_serialize', ['event']);
        $this->set('titleForLayout', $event['title']);
    }
}
