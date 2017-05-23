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
    public $components = [
    //    'Calendar',
        'Search.Prg',
        'RequestHandler'
    ];
    public $uses = ['Event'];
    public $eventFilter = [];
    public $adminActions = ['publish', 'approve', 'moderate'];

    public function initialize()
    {
        parent::initialize();
        // you don't need to log in to view events,
        // just to add & edit them
        $this->Auth->allow([
            'category', 'day', 'ics', 'index', 'location', 'search', 'tag', 'view'
        ]);
        $this->loadComponent('Search.Prg', [
            'actions' => ['search']
        ]);
    }

    private function isAdminOrAuthorPr($eventId)
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
            return $this->isAdminOrAuthorPr($this->request->params['named']['id']);
        }

        // Logged-in users can access everything else
        return true;
    }

    private function prepareEventFormPr($event)
    {
        $userId = $this->request->session()->read('Auth.User.id');
        $this->set([
            'previous_locations' => $this->Events->getPastLocations(),
            'userId' => $userId,
        ]);

        // prepare the tag helper
        $availableTags = $this->Events->Tags->find('all', [
            'order' => ['parent_id' => 'ASC']
            ])
            ->toArray();
        $this->set(compact('availableTags'));

        if ($this->request->action == 'add' || $this->request->action == 'edit_series') {
            $hasSeries = count(explode(',', $event->date)) > 1;
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
    }

    private function processCustomTagsPr($event)
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
                              ->where(['id' => $tagId])
                              ->toArray();
                if ($selectable) {
                    $this->request->data['data']['Tags'][] = $tagId;
                } else {
                    continue;
                }

                // Create the custom tag if it does not already exist
            } else {
                $newTag = $this->Events->Tags->newEntity();
                $newTag->name = $ct;
                $newTag->user_id = $this->Auth->user('id');
                $newTag->parent_id = 1012; // 'Unlisted' group
                $newTag->listed = 0;
                $newTag->selectable = 1;

                $this->Events->Tags->save($newTag);
                $this->request->data['data']['Tags'][] = $newTag->id;
            }
        }
        $this->request->data['data']['Tags'] = array_unique($this->request->data['data']['Tags']);
        $event->customTags = '';
    }

    private function processImageDataPr($event)
    {
        if ($event->id) {
            $eventId = $event->id;
        }
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

    private function prepareDatePickerPr($event)
    {
        // Prepare date picker
        if ($this->request->action == 'add' || $this->request->action == 'edit_series') {
            $dateFieldValues = [];
            if (empty($event->date)) {
                $defaultDate = 0; // Today
                $preselectedDates = '[]';
            } else {
                $dates = explode(',', $event->date);
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
            $this->set(compact('defaultDate', 'preselectedDates'));
            $event->date = implode(',', $dateFieldValues);
        } elseif ($this->action == 'edit') {
            list($year, $month, $day) = explode('-', $event->date);
            $event->date = "$month/$day/$year";
        }
    }

    public function datepickerPopulatedDates()
    {
        $results = $this->Events->getPopulatedDates();
        $dates = [];
        foreach ($results as $result) {
            list($year, $month, $day) = explode('-', $result->date);
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

        // prepare form
        $this->prepareEventFormPr($event);
        $this->processImageDataPr($event);
        $this->prepareDatePickerPr($event);

        if ($this->request->is(['patch', 'post', 'put'])) {
            // make sure the end time stays null if it needs to
            $this->uponFormSubmissionPr();
            $event = $this->Events->patchEntity($event, $this->request->getData());
            $this->processCustomTagsPr($event);
            if ($this->Events->save($event, [
                'associated' => ['Images']
            ])) {
                $this->Flash->success(__('The event has been saved.'));
            } else {
                $this->Flash->error(__('The event could not be saved. Please, try again.'));
            }
        }

        $users = $this->Events->Users->find('list');
        $categories = $this->Events->Categories->find('list');
        #$series = $this->Events->EventSeries->find('list');
        $this->set(compact('event', 'users', 'categories' /*'eventseries', */));
        $this->set('_serialize', ['event']);
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

    public function ics()
    {
        $this->response->type('text/calendar');
        $this->response->download('foo.bar');
        $this->viewBuilder()->setLayout('ics/default');
        return $this->render('/Events/ics/view');
    }

    public function moderate()
    {
        // Collect all unapproved events
        $unapproved = $this->Events
            ->find('all', [
            'contain' => ['Users', 'Categories', 'EventSeries', 'Images', 'Tags'],
            'order' => ['date' => 'ASC']
            ])
            ->where([
                'approved_by IS' => null,
                'published' => 0
            ])
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
        $this->set([
            'titleForLayout', '',
            'nextStartDate' => $this->Events->getNextStartDate($events)
        ]);
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

    public function month($month = null, $year = null)
    {
        if (!$month || !$year) {
            $this->redirect('/');
        }

        // Zero-pad day and month numbers
        $month = str_pad($month, 2, '0', STR_PAD_LEFT);
        $date = "$year-$month";
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
            'titleForLayout' => 'Events in '.$dateString,
            'displayedDate' => date('F, Y', $timestamp)
        ]);
    }

    private function uponFormSubmissionPr()
    {
        // kill the end time if it hasn't been set
        if (!$this->has['end_time']) {
            $this->request->data['time_end'] = null;
        }

        // auto-approve if posted by an admin
        $userId = $this->request->session()->read('Auth.User.id');
        $this->request->data['user_id'] = $userId;
        if ($this->request->session()->read('Auth.User.role') == 'admin') {
            $this->request->data['approved_by'] = $this->request->session()->read('Auth.User.id');
            $this->request->data['published'] = true;
        }
    }

    public function add()
    {
        $event = $this->Events->newEntity(['contain' => 'Images']);

        // prepare form
        $this->prepareEventFormPr($event);
        $this->processImageDataPr($event);
        $this->prepareDatePickerPr($event);

        if ($this->request->is(['patch', 'post', 'put'])) {
            $this->uponFormSubmissionPr();
            $event = $this->Events->patchEntity($event, $this->request->getData());
            $this->processCustomTagsPr($event);
            if ($this->Events->save($event)) {
                $this->Flash->success(__('The event has been saved.'));
            } else {
                $this->Flash->error(__('The event could not be saved. Please, try again.'));
            }
        }
        $users = $this->Events->Users->find('list');
        $categories = $this->Events->Categories->find('list');
        $this->set(compact('event', 'users', 'categories' /*'eventseries', */));
        $this->set('_serialize', ['event']);
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
            if ($tag-> id) {
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
}
