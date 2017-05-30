<?php
namespace App\Controller;

use App\Controller\AppController;

/**
 * EventSeries Controller
 *
 * @property \App\Model\Table\EventSeriesTable $EventSeries
 */
class EventSeriesController extends AppController
{

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
        $eventSeries = $this->EventSeries->get($id, [
            'contain' => ['Events']
        ]);
        $eventIds = $this->EventSeries->Events->find('list');
        $eventIds
            ->select('id')
            ->where(['series_id' => $id]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $eventSeries = $this->EventSeries->patchEntity($eventSeries, $this->request->getData());
            if ($this->EventSeries->save($eventSeries)) {
                $this->Flash->success(__('The event series has been saved.'));
                return $this->redirect(['action' => 'view', $id]);
            }
            $this->Flash->error(__('The event series could not be saved. Please, try again.'));
        }
        $users = $this->EventSeries->Users->find('list', ['limit' => 200]);
        $this->set(compact('eventIds', 'eventSeries', 'users'));
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
