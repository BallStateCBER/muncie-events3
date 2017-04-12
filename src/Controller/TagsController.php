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
    public $adminActions = ['get_name', 'getNodes', 'group_unlisted', 'manage', 'recover', 'remove', 'reorder', 'reparent', 'trace', 'edit', 'merge'];

    public function initialize()
    {
        parent::initialize();
        // non-users can still view tags
        $this->Auth->allow([
            'index', 'view'
        ]);
        $this->Auth->deny($this->adminActions);
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
        $authorOnly = [];
        if (in_array($this->action, $authorOnly)) {
            return $this->__isAdminOrAuthor($this->request->params['named']['id']);
        }

        // Logged-in users can access everything else
        return true;
    }

    public function getNodes()
    {
        // retrieve the node id that Ext JS posts via ajax
        $parent = isset($_POST['node']) ? intval($_POST['node']) : 0;

        // find all the nodes underneath the parent node defined above
        $nodes = $this->Tags
            ->find('children', ['for' => $parent])
            ->find('threaded')
            ->toArray();

        $rearrangedNodes = ['branches' => [], 'leaves' => []];
        foreach ($nodes as $key => &$node) {
            $tagId = $node->Tags['id'];

            // Check for events associated with this tag
            if ($node['Tags']['selectable']) {
                $count-> $this->Tags->EventsTags->find();
                $count
                    ->select()
                    ->where(['tag_id' => $tagId]);
                $node->Tags['no_events'] = $count == 0;
            }

            // Check for children
            $hasChildren = $this->Tags->childCount($tagId, true);
            if ($hasChildren) {
                $tagName = $node->Tags['name'];
                $rearrangedNodes['branches'][$tagName] = $node;
            } else {
                $rearrangedNodes['leaves'][$tagId] = $node;
            }
        }

        // Sort nodes by alphabetical branches, then alphabetical leaves
        ksort($rearrangedNodes['branches']);
        ksort($rearrangedNodes['leaves']);
        $nodes = array_merge(
            array_values($rearrangedNodes['branches']),
            array_values($rearrangedNodes['leaves'])
        );

        // Visually note categories with no data
        $showNoEvents = true;

        // send the nodes to our view
        $this->set(compact('nodes', 'showNoEvents'));

        $this->viewBuilder()->setLayout('blank');
    }

    public function manage()
    {
        $this->set([
            'titleForLayout' => 'Manage Tags'
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
        foreach ($tags as $tagName => $tag) {
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
         $this->viewBuilder()->setLayout('ajax');
         if (!$this->request->is('post') || (trim($this->request->data['name']) == '')) {
             $this->Flash->error(__('Please try again.'));
         }

         // Determine parent_id
         $parentName = $this->request->data['parent_name'];
         if ($parentName == '') {
             $rootParentId = null;
         } else {
             $rootParentId = $this->Tags->getIdFromName($parentName);
             if (!$rootParentId) {
                 $this->Flash->error(__('Parent tag '.$parentName.' not found.'));
             }
         }

         $class = 'success';
         $message = '';
         $inputted_names = explode("\n", trim(strtolower($this->request->data['name'])));
         $level = 0;
         $parents = [$rootParentId];
         foreach ($inputted_names as $line_num => $name) {
             $level = $this->Tags->getIndentLevel($name);

             // Discard any now-irrelevant data
             $parents = array_slice($parents, 0, $level + 1);

             // Determine this tag's parent_id
             if ($level == 0) {
                 $parentId = $rootParentId;
             } elseif (isset($parents[$level])) {
                 $parentId = $parents[$level];
             } else {
                 $this->Flash->error(__('Error with nested tag structure. Looks like there\'s an extra indent in line '.$line_num.': "'.$name.'".'));
                 continue;
             }

             // Strip leading/trailing whitespace and hyphens used for indenting
             $name = trim(ltrim($name, '-'));

             // Confirm that the tag name is non-blank and non-redundant
             if (! $name) {
                 continue;
             }
             $exists = $this->Tags->find();
             $exists
                ->select()
                ->where(['name' => $name])
                ->count();
             if ($exists) {
                 $this->Flash->error(__('Cannot create the tag "'.$name.'". because a tag with that name already exists.'));
                 continue;
             }

             // Add tag to database
             $this->Tags->create();
             $saveResult = $this->Tags->save(['Tag' => [
                 'name' => $name,
                 'parent_id' => $parentId,
                 'listed' => 1,
                 'selectable' => 1
             ]]);
             if ($saveResult) {
                 $this->Flash->success(__("Created tag #{$this->Tags->id}: $name"));
                 $parents[$level + 1] = $this->Tags->id;
             } else {
                 $this->Flash->error(__('Error creating the tag "'.$name.'"'));
             }
         }
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
