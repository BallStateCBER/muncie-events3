<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\I18n\Time;

/**
 * EventSeries Controller
 *
 * @property \App\Model\Table\EventSeriesTable $EventSeries
 */
class EventSeriesController extends AppController
{
    public $helpers = ['Html'];

    /**
     * Index method
     *
     * @return \Cake\Network\Response|null
     */
    public function index()
    {
        $this->paginate = [
            'contain' => ['Users']
        ];
        $eventSeries = $this->paginate($this->EventSeries);

        $this->set(compact('eventSeries'));
        $this->set('_serialize', ['eventSeries']);
    }

    /**
     * View method
     *
     * @param string|null $id Event Series id.
     * @return \Cake\Network\Response|null
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
        $this->set(['titleForLayout' => 'Event Series: '.$eventSeries->title]);
    }

    /**
     * Edit method
     *
     * @param string|null $id Event Series id.
     * @return \Cake\Network\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $eventSeries = $this->EventSeries->find('all', [
            'conditions' => ['id' => $id]
        ])->first();
        if ($eventSeries == null) {
            $this->Flash->error(__('Sorry, we can\'t find that event series.'));
            return $this->redirect(['controller' => 'events', 'action' => 'index']);
        }
        $eventSeries = $this->EventSeries->get($id, [
            'contain' => ['events' => [
                'sort' => ['date' => 'ASC']
                ]
            ]
        ]);
        $eventIds = $this->EventSeries->Events->find('list');
        $eventIds
            ->select('id')
            ->where(['series_id' => $id]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            if ($this->request->data['delete']) {
                if ($this->EventSeries->delete($eventSeries)) {
                    $this->Flash->success(__('The event series has been deleted.'));
                    return $this->redirect(['controller' => 'events', 'action' => 'index']);
                }
                $this->Flash->error(__('The event series could not be deleted. Please, try again.'));
            }

            if (isset($this->request->data['events'])) {
                $x = 0;
                foreach ($this->request->data['events'] as $event) {
                    if (!$event['edited']) {
                        $x = $x + 1;
                        continue;
                    }
                    if ($event['delete']) {
                        if ($this->EventSeries->Events->delete($eventSeries->events[$x])) {
                            $this->Flash->success(__('Event deleted: '.$event['id'].'.'));
                        }
                        $x = $x + 1;
                        continue;
                    }

                    $eventSeries->events[$x] = $this->EventSeries->Events->get($event['id']);
                    $eventSeries->events[$x]->date = new Time(implode('-', $event['date']));
                    $eventSeries->events[$x]->time_start = new Time(date('H:i',
                        strtotime($event['time_start']['hour'].':'.$event['time_start']['minute'].' '.$event['time_start']['meridian'])
                    ));
                    $eventSeries->events[$x]->title = $event['title'];

                    if ($this->EventSeries->Events->save($eventSeries->events[$x])) {
                        $this->Flash->success(__('Event #'.$event['id'].' has been saved.'));
                        $x = $x + 1;
                        continue;
                    }

                    $this->Flash->error(__('Event #'.$event['id'].' was not saved.'));
                    $x = $x + 1;
                }
            }

            $eventSeries->title = $this->request->data['title'];
            if ($this->EventSeries->save($eventSeries)) {
                $this->Flash->success(__('The event series has been saved.'));
                return $this->redirect(['action' => 'view', $id]);
            }

            $this->Flash->error(__('The event series has NOT been saved.'));
        }
        $users = $this->EventSeries->Users->find('list', ['limit' => 200]);
        $this->set(compact('eventIds', 'events', 'eventSeries', 'users'));
        $this->set('_serialize', ['eventSeries']);
        $this->set(['titleForLayout' => 'Edit Series: '.$eventSeries->title]);
    }

    /**
     * Delete method
     *
     * @param string|null $id Event Series id.
     * @return \Cake\Network\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $eventSeries = $this->EventSeries->get($id);
        if ($this->EventSeries->delete($eventSeries)) {
            $this->Flash->success(__('The event series has been deleted.'));
            return $this->redirect(['controller' => 'events', 'action' => 'index']);
        } else {
            $this->Flash->error(__('The event series could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
