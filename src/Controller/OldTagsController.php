<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\ORM\TableRegistry;

/**
 * Tags Controller
 *
 * @property \App\Model\Table\TagsTable $Tags
 */
class TagsController extends AppController
{
    public $adminActions = ['get_name', 'getnodes', 'group_unlisted', 'manage', 'recover', 'remove', 'reorder', 'reparent', 'trace', 'edit', 'merge'];

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

    public function getnodes()
    {
        $this->loadModel('EventsTags');
        // retrieve the node id that Ext JS posts via ajax
        $parent = isset($_POST['node']) ? intval($_POST['node']) : 0;

        // find all the nodes underneath the parent node defined above
        $nodes = $this->Tags
            ->find('children', ['for' => $parent])
            ->find('threaded')
            ->toArray();

        $rearrangedNodes = ['branches' => [], 'leaves' => []];
        foreach ($nodes as $key => &$node) {
            $tagId = $node->id;

            // Check for events associated with this tag
            if ($node->selectable) {
                $count = $this->EventsTags->find('all');
                $count
                    ->select()
                    ->where(['tag_id' => $tagId])
                    ->count();
                if ($node->no_events) {
                    $count == 0;
                }
            }

            // Check for children
            $hasChildren = $this->Tags->childCount($node);
            if ($hasChildren) {
                $tagName = $node->name;
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

    public function reorder()
    {

        // retrieve the node instructions from javascript
        // delta is the difference in position (1 = next node, -1 = previous node)

        $node = intval($_POST['node']);
        $delta = intval($_POST['delta']);

        if ($delta > 0) {
            $this->Tags->moveDown($node, abs($delta));
        } elseif ($delta < 0) {
            $this->Tags->moveUp($node, abs($delta));
        }

        // send success response
        exit('1');
    }

    public function reparent()
    {
        $node = intval($_POST['node']);
        $parent = ($_POST['parent'] == 'root') ? 0 : intval($_POST['parent']);
        $inUnlistedBefore = $this->Tags->isUnderUnlistedGroup($node);
        $inUnlistedAfter = ($_POST['parent'] == 'root') ? false : $this->Tags->isUnderUnlistedGroup($parent);
        $this->Tags->id = $node;

        // Moving out of the 'Unlisted' group
        if ($inUnlistedBefore && ! $inUnlistedAfter) {
            //echo 'Making listed.';
            $this->Tags->saveField('listed', 1);
        }

        // Moving into the 'Unlisted' group
        if (! $inUnlistedBefore && $inUnlistedAfter) {
            //echo 'Making unlisted.';
            $this->Tags->saveField('listed', 0);
        }

        // Move tag
        $this->Tags->saveField('parent_id', $parent);

        // If position == 0, then we move it straight to the top
        // otherwise we calculate the distance to move ($delta).
        // We have to check if $delta > 0 before moving due to a bug
        // in the tree behaviour (https://trac.cakephp.org/ticket/4037)
        $position = intval($_POST['position']);
        if ($position == 0) {
            $this->Tags->moveUp($node, true);
        } else {
            $count = $this->Tags->childCount($parent, true);
            $delta = $count-$position-1;
            if ($delta > 0) {
                $this->Tags->moveUp($node, $delta);
            }
        }

        // send success response
        exit('1');
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
     * Add method
     *
     * @return \Cake\Network\Response|null Redirects on successful add, renders view otherwise.
     */
     public function add()
     {
         $this->autoRender = false;
         $this->viewBuilder()->setLayout('ajax');
         if (!$this->request->is('post') || (trim($this->request->data['name']) == '')) {
             $this->Flash->error(__('Please try again.'));
         }

         // Determine parent_id
         $parentName = $this->request->data['parent_name'];
         if ($parentName == '') {
             $rootParentId = null;
         }
         if ($parentName != '') {
             $rootParentId = $this->Tags->getIdFromName($parentName);
             if (!$rootParentId) {
                 $this->Flash->error(__('Parent tag '.$parentName.' not found.'));
             }
         }

         $inputtedNames = explode("\n", trim(strtolower($this->request->data['name'])));
         $level = 0;
         $parents = [$rootParentId];
         foreach ($inputtedNames as $lineNum => $name) {
             $level = $this->Tags->getIndentLevel($name);

             // Discard any now-irrelevant data
             $parents = array_slice($parents, 0, $level + 1);

             // Determine this tag's parent_id
             if ($level == 0) {
                 $parentId = $rootParentId;
             } elseif (isset($parents[$level])) {
                 $parentId = $parents[$level];
             } else {
                 $this->Flash->error(__('Error with nested tag structure. Looks like there\'s an extra indent in line '.$lineNum.': "'.$name.'".'));
                 continue;
             }

             // Strip leading/trailing whitespace and hyphens used for indenting
             $name = trim(ltrim($name, '-'));

             // Confirm that the tag name is non-blank and non-redundant
             if (! $name) {
                 continue;
             }
             $exists = $this->Tags->find('list');
             $exists
                ->select('id')
                ->where(['name' => $name])
                ->toArray();
             if ($exists) {
                 $this->Flash->error(__('Cannot create the tag "'.$name.'". because a tag with that name already exists.'));
                 continue;
             }

             // Add tag to database
             $tags = TableRegistry::get('Tags');
             $tags = $tags->newEntity();
             $tags->name = $name;
             $tags->parent_id = $parentId;
             $tags->listed = 1;
             $tags->selectable = 1;
             if ($this->Tags->save($tags)) {
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

    public function remove($name)
    {
        $tagId = $this->Tags->getIdFromName($name);
        if (!$tagId) {
            $this->Flash->error(__("The tag '$name' does not exist (you may have already deleted it)."));
        } elseif ($this->Tags->delete($tagId)) {
            $this->Flash->success(__("Tag '$name' deleted."));
        } else {
            $this->Flash->error(__("There was an unexpected error deleting the '$name' tag."));
        }

        $this->set(['name' => $name]);
    }
}
