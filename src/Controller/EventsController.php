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
	public $auto_publish = false; // false puts new additions into moderation queue
	public $event_filter = [];
	public $admin_actions = ['publish', 'approve', 'moderate'];

    public function initialize()
    {
        parent::initialize();
        // you don't need to log in to view events,
        // just to add & edit them
        $this->Auth->allow([
            'index', 'view'
        ]);
    }

    private function __formatFormData()
    {
        $event = $this->request->getData('Event');

		if (! $event['time_start']) {
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

    private function __processCustomTags() {
        if (! isset($this->request->data['Events.custom_tags'])) {
            return;
        }
        $custom_tags = trim($this->request->data['Events.custom_tags']);
        if (empty($custom_tags)) {
            return;
        }
        $custom_tags = explode(',', $custom_tags);

        // Force lowercase and remove leading/trailing whitespace
        foreach ($custom_tags as &$ct) {
            $ct = strtolower(trim($ct));
        }
        unset($ct);

        // Remove duplicates
        $custom_tags = array_unique($custom_tags);

        $this->Event->Tag = $this->Event->Tag;
        foreach ($custom_tags as $ct) {
            // Skip over blank tags
            if ($ct == '') {
                continue;
            }

            // Get ID of existing tag, if it exists
            $tag_id = $this->Event->Tag->field('id', ['name' => $ct]);

            // Include this tag if it exists and is selectable
            if ($tag_id) {
                $selectable = $this->Event->Tag->field('selectable', ['id' => $tag_id]);
                if ($selectable) {
                    $this->request->data['Tag'][] = $tag_id;
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
        $available_tags = $this->Events->Tags->find('all', [
            'order' => ['parent_id' => 'ASC']
            ])
            ->toArray();
		$this->set([
            'available_tags' => $available_tags
		]);

        if ($this->request->action == 'add' || $this->request->action == 'edit_series') {
            $has_series = count(explode(',', $event['date'])) > 1;
            $has_end_time = isset($event['has_end_time']) ? $event['has_end_time'] : false;
        } elseif ($this->request->action == 'edit') {
            $has_series = isset($event['series_id']) ? (bool) $event['series_id'] : false;
            $has_end_time = isset($event['time_end']) && $event['time_end'];
        }

        $this->set([
            'has' => [
                'series' => $has_series,
                'end_time' => $has_end_time,
                'address' => isset($event['address']) && $event['address'],
                'cost' => isset($event['cost']) && $event['cost'],
                'ages' => isset($event['age_restriction']) && $event['age_restriction'],
                'source' => isset($event['source']) && $event['source']
            ]
        ]);

        // Fixes bug where midnight is saved as null
        if ($event['has_end_time']) {
            if (! $event['time_end']) {
                $event['time_end'] = '00:00:00';
            }
        } else {
            $event['time_end'] = null;
        }

        // Prepare date picker
        if ($this->request->action == 'add' || $this->request->action == 'edit_series') {
            $date_field_values = [];
            if (empty($event['date'])) {
                $default_date = 0; // Today
                $datepicker_preselected_dates = '[]';
            } else {
                $dates = explode(',', $event['date']);
                foreach ($dates as $date) {
                    list($year, $month, $day) = explode('-', $date);
                    if (! isset($default_date)) {
                        $default_date = "$month/$day/$year";
                    }
                    $date_field_values[] = "$month/$day/$year";
                }
                $dates_for_js = [];
                foreach ($date_field_values as $date) {
                    $dates_for_js[] = "'".$date."'";
                }
                $dates_for_js = implode(',', $dates_for_js);
                $datepicker_preselected_dates = "[$dates_for_js]";
            }
            $this->set([
                'default_date' => $default_date,
                'datepicker_preselected_dates' => $datepicker_preselected_dates
            ]);
            $event['date'] = implode(',', $date_field_values);
        } elseif ($this->action == 'edit') {
            list($year, $month, $day) = explode('-', $event['date']);
            $event['date'] = "$month/$day/$year";
        }
    }

    public function datepicker_populated_dates() {
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
        foreach ($events as $event) {
            $dates[] = get_object_vars($event->date);
        }
        foreach ($dates as $date) {
            $newDates[] = $date['date'];
        }
    	$event_keys = array_values($newDates);
    	if(count(array_unique($event_keys))<count($event_keys)) {
            $multiple_dates = true;
        } else {
            $multiple_dates = false;
        }
        $this->set([
            'events' => $events,
            'event_keys' => $event_keys,
            'multiple_dates' => $multiple_dates,
            'newDates' => $newDates
        ]);
        $this->set('titleForLayout', '');
    }

    public function location($location = null) {
        $now = Time::now();
        $events = $this->Events
            ->find('all', [
            'conditions' => ['location' => $location],
            'contain' => ['Users', 'Categories', 'EventSeries', 'Images', 'Tags'],
            'order' => ['date' => 'DESC']
            ])
            ->toArray();
        foreach ($events as $event) {
            $dates[] = get_object_vars($event->date);
        }
        foreach ($dates as $date) {
            $newDates[] = $date['date'];
        }
        $event_keys = array_values($newDates);
        if(count(array_unique($event_keys))<count($event_keys)) {
            $multiple_dates = true;
        } else {
            $multiple_dates = false;
        }
        $this->set([
            'events' => $events,
            'event_keys' => $event_keys,
            'location' => $location,
            'multiple_dates' => $multiple_dates,
            'newDates' => $newDates
        ]);
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
