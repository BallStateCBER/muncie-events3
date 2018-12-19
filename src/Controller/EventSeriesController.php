<?php
namespace App\Controller;

use Cake\I18n\Time;

/**
 * EventSeries Controller
 */
class EventSeriesController extends AppController
{
    public $helpers = ['Html'];

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
            'view'
        ]);
    }
    /**
     * Determines whether or not the user is authorized to make the current request
     *
     * @param \App\Model\Entity\User|null $user User entity
     * @return bool
     */
    public function isAuthorized($user = null)
    {
        if (isset($user)) {
            if ($user['role'] == 'admin') {
                return true;
            }
            $authorPages = [
                'edit'
            ];
            $action = $this->request->getParam('action');
            /* If the request isn't for an author-accessible page,
             * then it's for an admin-only page, and this user isn't an admin */
            if (!in_array($action, $authorPages)) {
                return false;
            }
            // Grant access only if this user is the event/series's author
            $entityId = $this->request->getParam('pass')[0];
            $entity = ($action == 'edit')
                ? $this->Events->get($entityId)
                : $this->EventSeries->get($entityId);

            $id = php_sapi_name() != 'cli' ? $user['id'] : $this->request->session()->read(['Auth.User.id']);

            return $entity->user_id === $id;
        }

        return false;
    }
    /**
     * Edit method
     *
     * @param string|null $id Event Series id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Http\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $eventSeries = $this->EventSeries->find('all', [
            'conditions' => ['id' => $id],
        ])->first();
        if ($eventSeries == null) {
            $this->Flash->error(__('Sorry, we can\'t find that event series.'));

            return $this->redirect(['controller' => 'events', 'action' => 'index']);
        }
        $eventSeries = $this->EventSeries->get($id, [
            'contain' => ['events' => [
                'sort' => ['start' => 'ASC']
                ]
            ]
        ]);
        $eventIds = $this->Events->find('list');
        $eventIds
            ->select('id')
            ->where(['series_id' => $id]);
        $users = $this->Users->find('list', ['limit' => 200]);
        $this->set(compact('eventIds', 'events', 'eventSeries', 'users'));
        $this->set('_serialize', ['eventSeries']);
        $this->set(['titleForLayout' => 'Edit Series: ' . $eventSeries->title]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            if ($this->request->getData('delete') == 1) {
                if ($this->EventSeries->delete($eventSeries)) {
                    $this->Flash->success(__('The event series has been deleted.'));
                    $events = $this->Events->find()
                        ->where(['series_id' => $id]);
                    foreach ($events as $event) {
                        $this->Events->delete($event);
                    }

                    return $this->redirect(['controller' => 'events', 'action' => 'index']);
                }
                $this->Flash->error(__('The event series could not be deleted. Please, try again.'));
            }

            $x = 0;
            foreach ($this->request->getData('events') as $event) {
                if ($event['edited'] != 1) {
                    $x = $x + 1;
                    continue;
                }
                if ($event['delete'] == 1) {
                    if ($this->Events->delete($eventSeries->events[$x])) {
                        $this->Flash->success(__('Event deleted: ' . $event['id'] . '.'));
                    }
                    $x = $x + 1;
                    continue;
                }

                $eventSeries->events[$x] = $this->Events->get($event['id']);
                $eventSeries->events[$x]->start = new Time(implode('-', $event['date']));
                $eventSeries->events[$x]->time_start = new Time(
                    date(
                        'H:i',
                        strtotime($event['time_start']['hour'] . ':' . $event['time_start']['minute'] . ' ' . $event['time_start']['meridian'])
                    )
                );
                $eventSeries->events[$x]->title = $event['title'] ?: $eventSeries->events[$x]->title;

                if ($this->Events->save($eventSeries->events[$x])) {
                    $this->Flash->success(__('Event #' . $event['id'] . ' has been saved.'));
                    $x = $x + 1;
                    continue;
                }

                $this->Flash->error(__('Event #' . $event['id'] . ' was not saved.'));
                $x = $x + 1;
            }

            $eventSeries->title = $this->request->getData('title');
            if ($this->EventSeries->save($eventSeries)) {
                $this->Flash->success(__('The event series has been saved.'));

                return $this->redirect(['action' => 'view', $id]);
            }

            $this->Flash->error(__('The event series has NOT been saved.'));
        }

        return null;
    }

    /**
     * View method
     *
     * @param string|null $id Event Series id.
     * @return \Cake\Http\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $eventSeries = $this->EventSeries->find('all', [
            'conditions' => ['id' => $id]
        ])->first();

        if ($eventSeries == null) {
            $this->Flash->error(__('Sorry, we can\'t find that event series.'));

            return $this->redirect(['controller' => 'events', 'action' => 'index']);
        }
        $eventSeries = $this->EventSeries->get($id, [
            'contain' => ['Events', 'Users']
        ]);

        $this->set('eventSeries', $eventSeries);
        $this->set('_serialize', ['eventSeries']);
        $this->set(['titleForLayout' => 'Event Series: ' . $eventSeries->title]);

        return null;
    }
}
