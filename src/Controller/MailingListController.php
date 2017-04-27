<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Routing\Router;
use Cake\ORM\TableRegistry;

/**
 * MailingList Controller
 *
 * @property \App\Model\Table\MailingListTable $MailingList
 */
class MailingListController extends AppController
{
    private function __setDefaultValues($recipient = null)
    {
        $this->request->data = $this->MailingList->getDefaultFormValues($recipient);
    }

    private function __readFormData($mailingList)
    {
        $this->loadModel('Categories');
        $this->loadModel('CategoriesMailingList');
        $allCategories = $this->MailingList->Categories->getAll();
        $mailingList->email = strtolower(trim($mailingList->email));

        // If joining for the first time with default settings
        if (isset($mailingList['settings'])) {
            if ($mailingList['settings'] == 'default') {
                $mailingList->weekly = 1;
                $mailingList->all_categories = 1;
                $mailingList->Categories = $allCategories;
            }
        }

        // All event types
        // If the user did not select 'all events', but has each category individually selected, set 'all_categories' to true
        $allCategoriesSelected = ($mailingList['event_categories'] == 'all');
        if (!$allCategoriesSelected) {
            $selectedCategoryCount = count($mailingList->selected_categories);
            $allCategoriesCount = count($allCategories);
            if ($selectedCategoryCount == $allCategoriesCount) {
                $allCategoriesSelected = true;
                $mailingList->all_categories = 1;
                $mailingList->Categories = $allCategories;
            }
        }

        // Custom event types
        if (isset($mailingList->selected_categories)) {
            $mailingList->Categories = array_keys($mailingList->selected_categories);
        }

        // Weekly frequency
        $weekly = $mailingList->weekly || $mailingList['frequency'] == 'weekly';
        $mailingList->weekly = $weekly;

        // Daily frequency
        $days = $this->MailingList->getDays();
        $daily = $mailingList['frequency'] == 'daily';
        foreach ($days as $code => $day) {
            $dailyCode = 'daily_'.$code;
            $value = $daily || $mailingList->$dailyCode;
            $mailingList->$dailyCode = $value;
        }
        $mailingList->new_subscriber = 1;

        return $mailingList;
    }


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
    public function join()
    {
        $titleForLayout = 'Join our Mailing List';
        $this->set('titleForLayout', $titleForLayout);
        $mailingList = $this->MailingList->newEntity();
        if ($this->request->is('post')) {
            $mailingList = $this->MailingList->patchEntity($mailingList, $this->request->getData());
            $mailingList = $this->__readFormData($mailingList);
            if ($this->MailingList->save($mailingList)) {
                $this->Flash->success(__('The mailing list has been saved.'));
                foreach ($mailingList->Categories as $category) {
                    $newCategory = $this->CategoriesMailingList->newEntity();
                    $newCategory->mailing_list_id = $mailingList->id;
                    if ($category->id) {
                        $newCategory->category_id = $category->id;
                    } elseif (isint($category)) {
                        $newCategory->category_id = $category;
                    }
                    $this->CategoriesMailingList->save($newCategory);
                }
            } else {
                $this->Flash->error(__('The mailing list could not be saved. Please, try again.'));
            }
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
