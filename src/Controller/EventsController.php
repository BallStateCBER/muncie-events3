<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\I18n\Date;
use Cake\I18n\Time;

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
    //	'Calendar',
    //	'Search.Prg',
    //	'RequestHandler'
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
            'index', 'location', 'view'
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

        $this->Event->Tag = $this->Event->Tag;
        foreach ($customTags as $ct) {
            // Skip over blank tags
            if ($ct == '') {
                continue;
            }

            // Get ID of existing tag, if it exists
            $tagId = $this->Event->Tag->field('id', ['name' => $ct]);

            // Include this tag if it exists and is selectable
            if ($tagId) {
                $selectable = $this->Event->Tag->field('selectable', ['id' => $tagId]);
                if ($selectable) {
                    $this->request->data['Tag'][] = $tagId;
                } else {
                    continue;
                }

            // Create the custom tag if it does not already exist
            } else {
                $this->Event->Tag->create();
                $this->Event->Tag->set([
                    'name' => $ct,
                    'user_id' => $this->Auth->user('id'),
                    'parent_id' => $this->Event->Tag->getUnlistedGroupId(), // 'Unlisted' group
                    'listed' => 0,
                    'selectable' => 1
                ]);
                $this->Event->Tag->save();
                $this->request->data['Tag'][] = $this->Event->Tag->id;
            }
        }
        $this->request->data['Tag'] = array_unique($this->request->data['Tag']);
        $this->request->data['Events.custom_tags'] = '';
    }

    private function __prepareEventForm()
    {
        $event = $this->request->getData('Event');
        $availableTags = $this->Events->Tags->find('all', [
            'order' => ['parent_id' => 'ASC']
            ])
            ->toArray();
        $this->set([
            'available_tags' => $availableTags
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
        } else {
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

    public function datepicker_populated_dates()
    {
        $results = $this->Event->getPopulatedDates();
        $dates = [];
        foreach ($results as $result) {
            list($year, $month, $day) = explode('-', $result->Event['date']);
            $dates["$month-$year"][] = $day;
        }
        $this->set(compact('dates'));
        $this->layout = 'blank';
    }

    // home page
    public function index()
    {
        $now = Time::now();
        $events = $this->Events
            ->find('all', [
            'contain' => ['Users', 'Categories', 'EventSeries', 'Images', 'Tags'],
            'order' => ['date' => 'ASC']
            ])
            ->where(['date >=' => $now])
            ->toArray();
        $this->indexEvents($events);
        $this->set('titleForLayout', '');
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

    public function view($id = null)
    {
        $event = $this->Events->get($id, [
            'contain' => ['Users', 'Categories', 'EventSeries', 'Images', 'Tags']
        ]);

        $this->set('event', $event);
        $this->set('_serialize', ['event']);
        $this->set('titleForLayout', $event['title']);
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

                #return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The event could not be saved. Please, try again.'));
            }
        }
        $users = $this->Events->Users->find('list');
        $categories = $this->Events->Categories->find('list');
        $series = $this->Events->EventSeries->find('list');
        $images = $this->Events->Images->find('list');
        $tags = $this->Events->Tags->find('list');
        $this->set(compact('event', 'users', 'categories', 'eventseries', 'images', 'tags'));
        $this->set('_serialize', ['event']);

        $this->__prepareEventForm();
        $this->set('titleForLayout', 'Submit an Event');
    }

    /**
     * Edit method
     *
     * @param string|null $id Event id.
     * @return \Cake\Network\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $event = $this->Events->get($id, [
            'contain' => ['Images', 'Tags']
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $event = $this->Events->patchEntity($event, $this->request->getData());
            if ($this->Events->save($event)) {
                $this->Flash->success(__('The event has been saved.'));

                //return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The event could not be saved. Please, try again.'));
            }
        }
        $users = $this->Events->Users->find('list');
        $categories = $this->Events->Categories->find('list');
        $series = $this->Events->EventSeries->find('list');
        $images = $this->Events->Images->find('list');
        $tags = $this->Events->Tags->find('list');
        $this->set(compact('event', 'users', 'categories', 'eventseries', 'images', 'tags'));
        $this->set('_serialize', ['event']);

        $this->__prepareEventForm();
        $this->set('titleForLayout', 'Edit Event');
    }

    /**
     * Delete method
     *
     * @param string|null $id Event id.
     * @return \Cake\Network\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
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
}
