<?php
namespace App\Controller;

use App\Controller\AppController;
use App\Model\Entity\Category;
use App\Model\Entity\Event;
use App\Model\Entity\Image;
use App\Model\Entity\Tag;
use App\Model\Entity\User;
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
        $this->Auth->allow([
            'add',
            'category',
            'datepickerPopulatedDates',
            'day',
            'ics',
            'index',
            'location',
            'month',
            'search',
            'searchAutocomplete',
            'tag',
            'today',
            'tomorrow',
            'view'
        ]);
        $this->loadComponent('Search.Prg', [
            'actions' => ['search']
        ]);
    }

    /**
     * Determines whether or not the user is authorized to make the current request
     *
     * @param User|null $user
     * @return bool
     */
    public function isAuthorized($user = null)
    {
        if ($user['role'] == 'admin') {
            return true;
        }

        $authorAccessiblePages = [
            'delete',
            'edit',
            'editseries'
        ];
        $action = $this->request->getParam('action');

        /* If the request isn't for an author-accessible page,
         * then it's for an admin-only page, and this user isn't an admin */
        if (!in_array($action, $authorAccessiblePages)) {
            return false;
        }

        // Grant access only if this user is the event/series's author
        $entityId = $this->request->getParam('pass')[0];
        $entity = ($action == 'editseries')
            ? $this->Events->EventSeries->get($entityId)
            : $this->Events->get($entityId);

        return $entity->user_id === $user['id'];
    }

    /**
     * Processes custom tag input and populates $this->request->data['data']['Tags']
     *
     * @param Event $event Event entity
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

        $tagIds = [];
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
                $tagIds[] = $tagId;
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
                $tagIds[] = $newTag->id;
            }
        }
        $tagIds = array_unique($tagIds);
        $this->request = $this->request->withData('Tags', $tagIds);
        $event->customTags = '';
    }

    /**
     * Sends the variables $dateFieldValues, $defaultDate, and $preselectedDates to the view
     *
     * @param Event $event Event entity
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
     * Sets various variables used in the event form
     *
     * @param Event $event Event entity
     * @return void
     */
    private function setEventForm($event)
    {
        $userId = $this->request->session()->read('Auth.User.id');
        $this->set([
            'previousLocations' => $this->Events->getPastLocations(),
            'userId' => $userId,
        ]);

        // Prepare the tag helper
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
     * Creates and/or removes associations between this event and its new/deleted images
     *
     * @param Event $event Event entity
     * @return void
     */
    private function setImageData($event)
    {
        $weight = 1;
        $place = 0;
        $imageData = $this->request->getData('newImages');
        if ($imageData) {
            foreach ($imageData as $imageId => $caption) {
                /** @var Image $newImage */
                $newImage = $this->Events->Images->get($imageId);
                $delete = $this->request->getData("delete.$imageId");
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
        $imageData = $this->request->getData('Image');
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
     * Marks the specified event as approved by an administrator
     *
     * @param int|null $id Event entity id
     * @return \Cake\Http\Response
     */
    public function approve($id = null)
    {
        $ids = $this->request->pass;
        if (empty($ids)) {
            $this->Flash->error('No events approved because no IDs were specified');

            return $this->redirect('/');
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
                $this->Flash->success("Event #$id approved <a href=\"$url\">Go to event page</a>");
            }
        }

        return $this->redirect($this->referer());
    }

    /**
     * Adds a new event
     *
     * @return void
     */
    public function add()
    {
        $event = $this->Events->newEntity();

        // Prepare form
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
            $this->uponFormSubmission();

            // count how many dates have been picked
            $dateInput = strlen($this->request->getData('date'));

            // a single event
            if ($dateInput == 10) {
                $event = $this->Events->patchEntity($event, $this->request->getData());
                $this->setCustomTags($event);
                $event->date = new Date($this->request->getData('date'));
                $event->series_id = null;
                if ($this->Events->save($event, [
                    'associated' => ['Images', 'Tags']
                ])) {
                    $this->Flash->success(__('The event has been saved.'));

                    return;
                }
            }

            // a series of multiple events
            if ($dateInput > 10) {
                // save the series itself
                $eventSeries = $this->Events->EventSeries->newEntity();
                $eventSeries = $this->Events->EventSeries->patchEntity($eventSeries, $this->request->getData());
                $eventSeries->title = $this->request->getData('title');
                $eventSeries->user_id = $this->request->session()->read('Auth.User.id');
                $eventSeries->published = ($this->request->session()->read('Auth.User.role') == 'admin') ? 1 : 0;
                $eventSeries->created = date('Y-m-d');
                $eventSeries->modified = date('Y-m-d');
                $this->Events->EventSeries->save($eventSeries);

                // now save every event
                $dates = explode(',', $this->request->getData('date'));
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

                $this->Flash->success(__('The event series has been saved.'));

                return;
            }

            // if neither a single event nor multiple-event series can be saved
            if (!$this->Events->save($event)) {
                $this->Flash->error(__('The event could not be saved. Please, try again.'));

                return;
            }
        }
    }

    /**
     * Displays events in the specified category
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
        /** @var Category $category */
        $category = $this->Events->Categories->find('all', [
            'conditions' => ['slug' => $slug]
        ])
            ->first();

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
    }

    /**
     * Shows the events taking place on the specified day
     *
     * @param string|null $month param for Events
     * @param string|null $day param for Events
     * @param string|null $year param for Events
     * @return \Cake\Http\Response|null
     */
    public function day($month = null, $day = null, $year = null)
    {
        if (! $year || ! $month || ! $day) {
            return $this->redirect('/');
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

        return null;
    }

    /**
     * Deletes an event
     *
     * @param int|null $id id for series
     * @return \Cake\Http\Response
     */
    public function delete($id = null)
    {
        $event = $this->Events->get($id);
        if ($this->Events->delete($event)) {
            $this->Flash->success(__('The event has been deleted.'));

            return $this->redirect('/');
        }
        $this->Flash->error(__('The event could not be deleted. Please, try again.'));

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Edits an event
     *
     * @param int $id id for series
     * @return void
     */
    public function edit($id = null)
    {
        $event = $this->Events->get($id, [
            'contain' => ['EventSeries', 'Images', 'Tags']
        ]);

        // Prepare form
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
            // Make sure the end time stays null if it needs to
            $this->uponFormSubmission();
            $event = $this->Events->patchEntity($event, $this->request->getData());
            $event->date = strtotime($this->request->getData('date'));
            $this->setCustomTags($event);
            if ($this->Events->save($event, [
                'associated' => ['EventSeries', 'Images', 'Tags']
            ])) {
                $event->date = $this->request->getData('date');
                $this->Flash->success(__('The event has been saved.'));

                return;
            }
            $this->Flash->error(__('The event could not be saved. Please, try again.'));

            return;
        }
    }

    /**
     * Edits the basic information about an event series
     *
     * @param int $seriesId id for series
     * @return void
     */
    public function editseries($seriesId)
    {
        // Get information about series
        $eventSeries = $this->Events->EventSeries->get($seriesId);
        if (!$eventSeries) {
            $msg = 'Sorry, it looks like you were trying to edit an event series that doesn\'t exist anymore.';
            $this->Flash->error($msg);

            return;
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
            // Save every event
            $newDates = explode(',', $this->request->getData('date'));
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

                $event->category_id = $this->request->getData('category_id');
                $newDate = new Date($date);
                $event->date = $newDate;
                $event->description = $this->request->getData('description');
                $event->location = $this->request->getData('location');
                $optional = ['time_end', 'age_restriction', 'cost', 'source', 'address', 'location_details'];
                foreach ($optional as $option) {
                    if ($this->request->getData($option)) {
                        if ($option = 'time_end') {
                            $time = $this->request->getData('time_end');
                            $timeString = $time['hour'] . ':' . $time['minute'] . ' ' . $time['meridian'];
                            $time = date('H:i:s', strtotime($timeString));
                            $event->time_end = new Time($time);
                            continue;
                        }
                        $event->$option = $this->request->getData($option);
                    }
                }
                $event->series_id = $seriesId;
                $time = $this->request->getData('time_start');
                $timeString = $time['hour'] . ':' . $time['minute'] . ' ' . $time['meridian'];
                $time = date('H:i:s', strtotime($timeString));
                $event->time_start = new Time($time);
                $event->title = $this->request->getData('title');

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
            $series->title = $this->request->getData('event_series.title');
            if ($this->Events->EventSeries->save($series)) {
                $this->Flash->success(__("The event series '$series->title' was saved."));

                return;
            }
            if (!$this->Events->EventSeries->save($series)) {
                $this->Flash->error(__("The event series '$series->title' was not saved."));

                return;
            }
        }
    }

    /**
     * Returns an address associated with the specified location name
     *
     * @param string $location we need address
     * @return void
     */
    public function getAddress($location = '')
    {
        $this->viewBuilder()->setLayout('blank');
        $this->set('address', $this->Events->getAddress($location));
    }

    /**
     * Gets events on the selected date and runs indexEvents()
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
     * Shows a page of events
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
     * Shows events taking place at the specified location, optionally limited to past or future events
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
     * Shows events needing administrator approval
     *
     * @return void
     */
    public function moderate()
    {
        // Collect all unapproved events
        $unapproved = $this->Events
            ->find('all', [
                'contain' => ['Users', 'Categories', 'EventSeries', 'Images', 'Tags'],
                'order' => ['date' => 'ASC']
            ])
            ->where(['Events.approved_by' => null])
            ->orWhere(['Events.published' => '0'])
            ->toArray();

        /* Find sets of identical events (belonging to the same series and with the same modified date)
         * and remove all but the first */
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
     * Shows all events for the specified month
     *
     * @param string|null $month month of Event
     * @param string|null $year year of Event
     * @return \Cake\Http\Response|null
     */
    public function month($month = null, $year = null)
    {
        if (!$month || !$year) {
            return $this->redirect('/');
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

        return null;
    }

    /**
     * Shows all of the locations associated with past events
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
     * Shows events that match a provided search term
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
            'contain' => ['Users', 'Categories', 'EventSeries', 'Images', 'Tags']
        ])
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

        // Determine if there are events in the opposite direction
        if ($direction == 'past' || $direction = 'future') {
            $whereKey = ($direction == 'future') ? 'date <' : 'date >=';
            $oppositeCount = $this->Events->find('search', ['search' => $filter])
                ->where([$whereKey => date('Y-m-d')])
                ->count();
            $this->set('oppositeEvents', $oppositeCount);
        }

        $tags = $this->Events->Tags->find('search', [
            'search' => $filter
        ]);
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
     * Provides an autocomplete suggestion for a partial search term
     *
     * @return void
     */
    public function searchAutocomplete()
    {
        $stringToComplete = filter_input(INPUT_GET, 'term');
        $limit = 10;

        // The search term will be compared via LIKE to each of these, in order, until $limit tags are found
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
     * Shows the events with a specified tag
     *
     * @param string|null $slug tag slug
     * @param string|null $direction of results
     * @return void
     */
    public function tag($slug = '', $direction = null)
    {
        $dir = $direction == 'past' ? 'ASC' : 'DESC';
        $date = $direction == 'past' ? '<' : '>=';
        $oppDir = $direction == 'past' ? 'DESC' : 'ASC';
        $oppDate = $direction == 'past' ? '>=' : '<';
        $opposite = $direction == 'past' ? 'upcoming' : 'past';

        // Get tag
        $this->loadModel('Tags');
        $tagId = $this->Tags->getIdFromSlug($slug);
        /** @var Tag $tag */
        $tag = $this->Events->Tags->find('all', [
            'conditions' => ['id' => $tagId],
            'fields' => ['id', 'name'],
            'contain' => false
        ])->first();
        if (empty($tag)) {
            $this->Flash->error("Sorry, but we couldn't find that tag ($slug)");

            return;
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
     * Shows the events taking place today
     *
     * @return \Cake\Http\Response
     */
    public function today()
    {
        return $this->redirect('/events/day/' . date('m') . '/' . date('d') . '/' . date('Y'));
    }

    /**
     * Shows the events taking place tomorrow
     *
     * @return \Cake\Http\Response
     */
    public function tomorrow()
    {
        $tomorrow = date('m-d-Y', strtotime('+1 day'));
        $tomorrow = explode('-', $tomorrow);

        return $this->redirect('/events/day/' . $tomorrow[0] . '/' . $tomorrow[1] . '/' . $tomorrow[2]);
    }

    /**
     * Conditionally removes time_end and auto-approves the submitted event
     *
     * @return void
     */
    private function uponFormSubmission()
    {
        // Kill the end time if it hasn't been set
        if (!$this->request->getData('has_end_time')) {
            $this->request = $this->request->withData('time_end', null);
        }

        // Auto-approve if posted by an admin
        $userId = $this->request->session()->read('Auth.User.id') ?: null;
        $this->request = $this->request->withData('user_id', $userId);
        if ($this->request->session()->read('Auth.User.role') == 'admin') {
            $this->request = $this->request->withData('approved_by', $userId);
            $this->request = $this->request->withData('published', true);
        }
    }

    /**
     * Shows a specific event
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
