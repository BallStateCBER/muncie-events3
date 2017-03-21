<?php
namespace App\Controller;

use App\Controller\AppController;

/**
 * MailingList Controller
 *
 * @property \App\Model\Table\MailingListTable $MailingList
 */
class MailingListController extends AppController
{

    /**
     * Index method
     *
     * @return \Cake\Network\Response|null
     */
    public function index()
    {
        $mailingList = $this->paginate($this->MailingList);

        $this->set(compact('mailingList'));
        $this->set('_serialize', ['mailingList']);
    }

    /**
     * View method
     *
     * @param string|null $id Mailing List id.
     * @return \Cake\Network\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $mailingList = $this->MailingList->get($id, [
            'contain' => ['Categories', 'Users']
        ]);

        $this->set('mailingList', $mailingList);
        $this->set('_serialize', ['mailingList']);
    }

    /**
     * Add method
     *
     * @return \Cake\Network\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $mailingList = $this->MailingList->newEntity();
        if ($this->request->is('post')) {
            $mailingList = $this->MailingList->patchEntity($mailingList, $this->request->getData());
            if ($this->MailingList->save($mailingList)) {
                $this->Flash->success(__('The mailing list has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The mailing list could not be saved. Please, try again.'));
        }
        $categories = $this->MailingList->Categories->find('list', ['limit' => 200]);
        $this->set(compact('mailingList', 'categories'));
        $this->set('_serialize', ['mailingList']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Mailing List id.
     * @return \Cake\Network\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $mailingList = $this->MailingList->get($id, [
            'contain' => ['Categories']
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $mailingList = $this->MailingList->patchEntity($mailingList, $this->request->getData());
            if ($this->MailingList->save($mailingList)) {
                $this->Flash->success(__('The mailing list has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The mailing list could not be saved. Please, try again.'));
        }
        $categories = $this->MailingList->Categories->find('list', ['limit' => 200]);
        $this->set(compact('mailingList', 'categories'));
        $this->set('_serialize', ['mailingList']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Mailing List id.
     * @return \Cake\Network\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $mailingList = $this->MailingList->get($id);
        if ($this->MailingList->delete($mailingList)) {
            $this->Flash->success(__('The mailing list has been deleted.'));
        } else {
            $this->Flash->error(__('The mailing list could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
