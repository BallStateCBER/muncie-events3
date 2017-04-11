<?php
namespace App\Controller;

use App\Controller\AppController;

/**
 * Tags Controller
 *
 * @property \App\Model\Table\TagsTable $Tags
 */
class TagsController extends AppController
{
    public function initialize()
    {
        parent::initialize();
        // non-users can still view tags
        $this->Auth->allow([
            'index', 'view'
        ]);
    }
    public function index($direction = 'future', $category = 'all')
    {
        if ($direction != 'future' && $direction != 'past') {
            $direction = 'future';
        }
        $filters = compact('direction');
        if ($category != 'all') {
            $filters['categories'] = $category;
        }
        $tags = $this->Tags->getWithCounts($filters, 'alpha');
        $tagsByFirstLetter = [];
        foreach ($tags as $tag_name => $tag) {
            $firstLetter = ctype_alpha($tag['name'][0]) ? $tag['name'][0] : '#';
            $tagsByFirstLetter[$firstLetter][$tag['name']] = $tag;
        }
        $directionAdjective = ($direction == 'future' ? 'upcoming' : 'past');
        $titleForLayout = 'Tags (';
        $titleForLayout .= ucfirst($directionAdjective);
        $this->loadModel('Categories');
        if ($category != 'all' && $categoryName = $this->Categories->getName($category)) {
            $titleForLayout .= ' '.str_replace(' Events', '', ucwords($categoryName));
        }
        $titleForLayout .= ' Events)';
        $this->set(compact(
            'titleForLayout',
            'tags',
            'tagsByFirstLetter',
            'direction',
            'directionAdjective',
            'category'
        ));
        $this->loadModel('Categories');
        $this->set([
            'categories' => $this->Categories->getAll(),
            'categoriesWithTags' => $this->Tags->getCategoriesWithTags($direction)
        ]);
    }

    /**
     * View method
     *
     * @param string|null $id Tag id.
     * @return \Cake\Network\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $tag = $this->Tags->get($id, [
            'contain' => ['ParentTags', 'Users', 'Events', 'ChildTags']
        ]);

        $this->set('tag', $tag);
        $this->set('_serialize', ['tag']);
    }

    /**
     * Add method
     *
     * @return \Cake\Network\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $tag = $this->Tags->newEntity();
        if ($this->request->is('post')) {
            $tag = $this->Tags->patchEntity($tag, $this->request->getData());
            if ($this->Tags->save($tag)) {
                $this->Flash->success(__('The tag has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The tag could not be saved. Please, try again.'));
        }
        $parentTags = $this->Tags->ParentTags->find('list', ['limit' => 200]);
        $users = $this->Tags->Users->find('list', ['limit' => 200]);
        $events = $this->Tags->Events->find('list', ['limit' => 200]);
        $this->set(compact('tag', 'parentTags', 'users', 'events'));
        $this->set('_serialize', ['tag']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Tag id.
     * @return \Cake\Network\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $tag = $this->Tags->get($id, [
            'contain' => ['Events']
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $tag = $this->Tags->patchEntity($tag, $this->request->getData());
            if ($this->Tags->save($tag)) {
                $this->Flash->success(__('The tag has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The tag could not be saved. Please, try again.'));
        }
        $parentTags = $this->Tags->ParentTags->find('list', ['limit' => 200]);
        $users = $this->Tags->Users->find('list', ['limit' => 200]);
        $events = $this->Tags->Events->find('list', ['limit' => 200]);
        $this->set(compact('tag', 'parentTags', 'users', 'events'));
        $this->set('_serialize', ['tag']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Tag id.
     * @return \Cake\Network\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $tag = $this->Tags->get($id);
        if ($this->Tags->delete($tag)) {
            $this->Flash->success(__('The tag has been deleted.'));
        } else {
            $this->Flash->error(__('The tag could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
