<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Http\Response;
use Cake\ORM\TableRegistry;

/**
 * Tags Controller
 *
 * @property \App\Model\Table\TagsTable $Tags
 */
class TagsController extends AppController
{
    public $adminActions = ['getName', 'getnodes', 'groupUnlisted', 'manage', 'recover', 'remove', 'reorder', 'reparent', 'trace', 'edit', 'merge'];

    /**
     * Initialize hook method.
     *
     * @return void
     */
    public function initialize()
    {
        parent::initialize();
        $this->Auth->allow([
            'index'
        ]);

        if (!$this->isAuthorized()) {
            $this->Flash->error('You are not authorized to view that page.');
            $this->redirect('/');
        }
    }

    /**
     * Auth settings for the tag manager
     *
     * @return bool
     */
    public function isAuthorized()
    {
        // testing turn-off!
        if (php_sapi_name() == 'cli') {
            return true;
        }

        // Admins can access everything
        if ($this->Auth->user('role') == 'admin' || $this->request->session()->read(['Auth.User.role'])) {
            return true;

        // Some actions are admin-only
        } elseif (in_array($this->request->action, $this->adminActions)) {
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

    /**
     * tagmanager view
     *
     * @return void
     */
    public function manage()
    {
        $this->set([
            'titleForLayout' => 'Manage Tags'
        ]);
    }

    /**
     * tags index
     *
     * @param string $direction of the tags
     * @param string $category of the tags
     * @return void
     */
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
        foreach ($tags as $tag) {
            $firstLetter = ctype_alpha($tag['name'][0]) ? $tag['name'][0] : '#';
            $tagsByFirstLetter[$firstLetter][$tag['name']] = $tag;
        }
        $directionAdjective = ($direction == 'future' ? 'upcoming' : 'past');
        $titleForLayout = 'Tags (';
        $titleForLayout .= ucfirst($directionAdjective);
        $this->loadModel('Categories');
        if ($category != 'all' && $categoryName = $this->Categories->getName($category)) {
            $titleForLayout .= ' ' . str_replace(' Events', '', ucwords($categoryName));
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
     * autoComplete method
     *
     * @return void
     */
    public function autoComplete()
    {
        $stringToComplete = filter_input(INPUT_GET, 'term');
        $limit = 10;

        // Tag.name will be compared via LIKE to each of these,
        // in order, until $limit tags are found.
        $likeConditions = [
            $stringToComplete,
            $stringToComplete . ' %',
            $stringToComplete . '%',
            '% ' . $stringToComplete . '%',
            '%' . $stringToComplete . '%'
        ];

        // Collect tags up to $limit
        $tags = [];
        foreach ($likeConditions as $like) {
            if (count($tags) == $limit) {
                break;
            }
            $conditions = ['name LIKE' => $like];
            $results = $this->Tags->find()
                ->where($conditions)
                ->limit($limit - count($tags));
            if (!empty($tags)) {
                foreach (array_keys($tags) as $tag) {
                    $results = $results->andWhere(['id !=' => $tag]);
                }
            }
            $x = 0;
            foreach ($results as $result) {
                $tags[$result->id] = [
                    'label' => $result->name,
                    'value' => $result->id
                ];

                $tag = $result->id;
                $tag = [
                    'label' => $result->name,
                    'value' => $result->id
                ];
                $this->set([$x => $tag]);
                $x = $x + 1;
            }
        }

        $this->set(compact('tags'));
        $this->viewBuilder()->setLayout('ajax');
    }

    /**
     * recover the tag tree
     *
     * @return void
     */
    public function recover()
    {
        list($startUsec, $startSec) = explode(" ", microtime());
        set_time_limit(3600);
        $this->Tags->recover();
        list($endUsec, $endSec) = explode(" ", microtime());
        $startTime = $startUsec + $startSec;
        $endTime = $endUsec + $endSec;
        $loadingTime = $endTime - $startTime;
        $minutes = round($loadingTime / 60, 2);
        $this->render('/Tags/flash');
        $this->Flash->success("Done recovering tag tree (took $minutes minutes).");
    }

    /**
     * Places any root-level unlisted tags in the 'unlisted' tag group
     *
     * @return void
     */
    public function groupUnlisted()
    {
        list($startUsec, $startSec) = explode(" ", microtime());
        set_time_limit(3600);

        // Take all unlisted tags without parents and place them under the 'unlisted' group
        $unlistedGroupId = $this->Tags->getUnlistedGroupId();
        $deleteGroupId = $this->Tags->getDeleteGroupId();
        $results = $this->Tags->find()
            ->where([
                'listed' => 0,
                'OR' => [['parent_id' => 0], ['parent_id IS' => null]],
                'AND' => [['id IS NOT' => $unlistedGroupId], ['id IS NOT' => $deleteGroupId]]
            ])
            ->limit(20)
            ->toArray();
        foreach ($results as $result) {
            $result->parent_id = $unlistedGroupId;
            $this->Tags->save($result);
        }

        list($endUsec, $endSec) = explode(" ", microtime());
        $startTime = $startUsec + $startSec;
        $endTime = $endUsec + $endSec;
        $loadingTime = $endTime - $startTime;
        $minutes = round($loadingTime / 60, 2);

        $message = 'Regrouped ' . count($results) . " unlisted tags (took $minutes minutes).";
        $more = $this->Tags->find()
            ->where([
                'listed' => 0,
                'OR' => [['parent_id' => 0], ['parent_id IS' => null]],
                'AND' => [['id IS NOT' => $unlistedGroupId], ['id IS NOT' => $deleteGroupId]]
            ])
            ->count();
        if ($more) {
            $message .= ' There\'s ' . $more . ' more unlisted tag' . ($more == 1 ? '' : 's') . ' left to move. Please run this function again.';
        }
        $this->Flash->success($message);
        $this->render('/Tags/flash');
    }

    /**
     * get nodes of tags in order to order or reorder them
     *
     * @return void
     */
    public function getnodes()
    {
        $this->loadModel('EventsTags');
        $node = filter_input(INPUT_POST, 'node');
        // retrieve the node id that Ext JS posts via ajax
        $parent = isset($node) ? intval($node) : 0;

        // find all the nodes underneath the parent node defined above
        if ($parent != 0) {
            $nodes = $this->Tags
                ->find('children', ['for' => $parent])
                ->find('threaded')
                ->toArray();
        }
        if ($parent == 0) {
            $nodes = $this->Tags
                ->find('threaded')
                ->toArray();
        }

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
            }
            if (!$hasChildren) {
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
    }

    /**
     * reorder tags
     *
     * @return void
     */
    public function reorder()
    {
        // retrieve the node instructions from javascript
        // delta is the difference in position (1 = next node, -1 = previous node)
        $node = intval($_POST['node']);
        $delta = intval($_POST['delta']);

        $node = $this->Tags->get($node);

        if ($delta > 0) {
            $this->Tags->moveDown($node, abs($delta));
        } elseif ($delta < 0) {
            $this->Tags->moveUp($node, abs($delta));
        }

        // send success response
        exit('1');
    }

    /**
     * give orphan tags new parents
     *
     * @return void
     */
    public function reparent()
    {
        $node = intval($_POST['node']);
        $parent = ($_POST['parent'] == 'root') ? 0 : intval($_POST['parent']);
        $parent = $this->Tags->get($parent);
        $inUnlistedBefore = $this->Tags->isUnderUnlistedGroup($node);
        $inUnlistedAfter = ($_POST['parent'] == 'root') ? false : $this->Tags->isUnderUnlistedGroup($parent->id);
        $tag = $this->Tags->get($node);

        // Moving out of the 'Unlisted' group
        if ($inUnlistedBefore && ! $inUnlistedAfter) {
            //echo 'Making listed.';
            $tag->listed = 1;
            $this->Tags->save($tag);
        }

        // Moving into the 'Unlisted' group
        if (! $inUnlistedBefore && $inUnlistedAfter) {
            //echo 'Making unlisted.';
            $tag->listed = 0;
            $this->Tags->save($tag);
        }

        // Move tag
        $tag->parent_id = $parent->id;
        $this->Tags->save($tag);

        // If position == 0, then we move it straight to the top
        // otherwise we calculate the distance to move ($delta).
        // We have to check if $delta > 0 before moving due to a bug
        // in the tree behaviour (https://trac.cakephp.org/ticket/4037)
        $position = intval($_POST['position']);
        if ($position == 0) {
            $this->Tags->moveUp($tag, true);
        }
        if ($position != 0) {
            $count = $this->Tags->childCount($parent, true);
            $delta = $count - $position - 1;
            if ($delta > 0) {
                $this->Tags->moveUp($tag, $delta);
            }
        }

        // send success response
        exit('1');
    }

    /**
     * Returns a path from the root of the Tag tree to the tag with the provided name
     *
     * @param string $tagName of the tag you need traced
     * @return void
     */
    public function trace($tagName = '')
    {
        $path = [];
        $tagId = $this->Tags->getIdFromName($tagName);
        $targetTag = $this->Tags->get($tagId);
        if ($targetTag) {
            $targetTagId = $targetTag->id;
            $parentId = $targetTag->parent_id;
            $path[] = "{$targetTag->name} ({$targetTagId})";
            if ($parentId) {
                $rootFound = false;
                while (!$rootFound) {
                    $parent = $this->Tags->getTagFromId($parentId);
                    if ($parent) {
                        $path[] = "{$parent->name} ({$parent->id})";
                        if (!$parentId = $parent->parent_id) {
                            $rootFound = true;
                        }
                    }
                    if (!$parent) {
                        $path[] = "(Parent data tag with id $parentId not found)";
                        break;
                    }
                }
            }
        }
        if (!$targetTag) {
            $path[] = "(Tag named '$tagName' not found)";
        }
#        $this->viewBuilder()->setLayout('ajax');
        $path = array_reverse($path);
        $this->set(compact('path', 'targetTag', 'parent'));
    }

    /**
     * Returns the name of the Tag with id $id, used by the tag manager
     *
     * @param int $id of tag name you want
     * @return void
     */
    public function getName($id)
    {
        $tag = $this->Tags->get($id);
        $tag ? $name = $tag->name : $name = "Error: Tag does not exist";
        $this->set(compact('name'));
        $this->viewBuilder()->setLayout('ajax');
    }

    /**
     * remove individual tags
     *
     * @param string $name of the tag you want gone
     * @return void
     */
    public function remove($name)
    {
        if (!$name) {
            $message = "You have not entered a tag name. Please try again.";
            $class = "error";
        }
        $tagId = $this->Tags->getIdFromName($name);
        if (!$tagId) {
            $message = "The tag \"$name\" does not exist (you may have already deleted it).";
            $class = 'error';
        }
        $tag = $this->Tags->get($tagId);
        if ($tag) {
            $message = "There was an unexpected error deleting the \"$name\" tag.";
            $class = 'error';
            if ($this->Tags->delete($tag)) {
                $message = "Tag \"$name\" deleted.";
                $class = 'success';
            }
        }
        $this->Flash->$class($message);
        $this->render('/Tags/flash');
    }

    /**
     * Removes all unlisted, unused, root-level tags with no children
     *
     * @return void
     */
    public function removeUnlistedUnused()
    {
        $deleteGroupId = $this->Tags->getDeleteGroupId();
        $unlistedGroupId = $this->Tags->getUnlistedGroupId();
        $tags = $this->Tags->find()
            ->where([
                'listed' => 0,
                'OR' => [['parent_id' => 0], ['parent_id IS' => null]],
                'AND' => [['id IS NOT' => $unlistedGroupId], ['id IS NOT' => $deleteGroupId], ['id NOT IN' => $this->Tags->getUsedTagIds()]]
            ])
            ->toArray();
        $skippedTags = $deletedTags = [];
        foreach ($tags as $tag) {
            if ($this->Tags->childCount($tag)) {
                $skippedTags[] = $tag->name;
                continue;
            }
            $this->Tags->delete($tag);
            $deletedTags[] = $tag->name;
        }
        if (empty($deletedTags)) {
            $message = 'No tags found that were both unlisted and unused.';
        }
        if (!empty($deletedTags)) {
            $message = 'Deleted the following tags: <br />- ';
            $message .= implode('<br />- ', $deletedTags);
        }
        if (!empty($skippedTags)) {
            $message .= '<br />&nbsp;<br />Did not delete the following tags, since they have child-tags: <br />- ';
            $message .= implode('<br />- ', $skippedTags);
        }
        $this->Flash->success($message);
        $this->render('/Tags/flash');
    }

    /**
     * Finds duplicate tags and merges the tags with higher IDs into those with the lowest ID
     *
     * @return void
     */
    public function duplicates()
    {
        // List all tag names and corresponding id(s)
        $tags = $this->Tags->find()
                ->order(['name' => 'ASC'])
                ->toArray();
        $tagsArranged = [];
        foreach ($tags as $tag) {
            if (isset($tagsArranged[$tag->name])) {
                $tagsArranged[$tag->name][] = $tag->id;
                continue;
            }
            $tagsArranged[$tag->name] = [$tag->id];
        }

        // Find duplicate tags
        $message = '';
        $recoverTree = false;
        foreach ($tagsArranged as $tagName => $tagIds) {
            if (count($tagIds) < 2) {
                continue;
            }

            // Aha! Duplicates!
            $message .= "Tag \"$tagName\" has IDs: " . implode(', ', $tagIds) . '. ';
            $firstTag = array_shift($tagIds);
            foreach ($tagIds as $tag => $tagId) {
                // find & remove the old tag
                $tag = $this->Tags->get($tagId);
                $this->Tags->delete($tag);

                // reassign any children to the first tag
                $children = $this->Tags->find()
                    ->where(['parent_id' => $tagId])
                    ->toArray();

                foreach ($children as $child) {
                    $child->parent_id = $firstTag;
                    $this->Tags->save($child);
                }

                // associations?
                $events = $this->Tags->EventsTags->find()
                    ->where(['tag_id' => $tagId])
                    ->toArray();

                foreach ($events as $event) {
                    $event->tag_id = $firstTag;
                    $this->Tags->EventsTags->save($event);
                }
            }

            $message .= " Duplicate \"$tagName\" tags have been deleted and merged to tag #$firstTag.";
        }

        if ($message == '') {
            $message = 'No duplicate tags found.';
        }

        // If tags have been reparented, recover tag tree
        if ($recoverTree) {
            $this->Tags->recover();
        }

        $this->set(compact('tagsArranged'));

        $this->Flash->success($message);
        $this->render('/Tags/flash');
    }

    /**
     * Turns all associations with Tag $tagId into associations with Tag $merge_into_id
     * and deletes Tag $tagId, and moves any child tags under Tag $merge_into_id.
     *
     * @param string $removedTagName tag lost in merge
     * @param string $retainedTagName tag kept in merge
     * @return void
     */
    public function merge($removedTagName = '', $retainedTagName = '')
    {
        $this->loadModel('EventsTags');
        $removedTagName = trim($removedTagName);
        $retainedTagName = trim($retainedTagName);

        // Verify input
        if ($removedTagName == '') {
            $this->Flash->error('No name provided for the tag to be removed.');
        }
        $removedTagId = $this->Tags->getIdFromName($removedTagName);
        if (!$removedTagId) {
            $this->Flash->error("The tag \"$removedTagName\" could not be found.");
        }
        if ($retainedTagName == '') {
            $this->Flash->error('No name provided for the tag to be retained.');
        }
        if ($retainedTagName != '') {
            $retainedTagId = $this->Tags->getIdFromName($retainedTagName);
            if (!$retainedTagId) {
                $this->Flash->error("The tag \"$retainedTagName\" could not be found.");
            }
        }
        if ($removedTagId == $retainedTagId) {
            $this->Flash->error("Cannot merge \"$retainedTagName\" into itself.");
        }

        $message = '';
        $class = 'success';

        // Switch event associations
        $associatedCount = $this->EventsTags->find()
            ->where(['tag_id' => $removedTagId])
            ->count();
        if ($associatedCount) {
            $results = $this->EventsTags->find()
                ->where(['tag_id' => $removedTagId])
                ->toArray();

            foreach ($results as $result) {
                $result->tag_id = $retainedTagId;
                $this->EventsTags->save($result);
            }

            $message .= "Changed association with \"$removedTagName\" into \"$retainedTagName\" in $associatedCount event" . ($associatedCount == 1 ? '' : 's') . '. ';
        }
        if (!$associatedCount) {
            $message .= 'No associated events to edit. ';
        }

        // Move child tags
        $children = $this->Tags->find('list', [
            'conditions' => ['parent_id' => $removedTagId]
        ]);
        if (empty($children)) {
            $message .= 'No child-tags to move.<br />';
        }
        if (!empty($children)) {
            foreach ($children as $childId => $childName) {
                $childTag = $this->Tags->get($childId);
                $childTag->parent_id = $retainedTagId;
                if ($this->Tags->save($childTag)) {
                    $message .= "Moved \"$childName\" from under \"$removedTagName\" to under \"$retainedTagName\". ";
                    continue;
                }
                $class = 'error';
                $message .= "Error moving \"$childName\" from under \"$removedTagName\" to under \"$retainedTagName\". ";
            }
        }

        $removedTag = $this->Tags->get($removedTagId);
        // Delete tag
        if ($class == 'success') {
            if ($this->Tags->delete($removedTag)) {
                $message .= "Removed \"$removedTagName\".";
            }
            if (!$this->Tags->delete($removedTag)) {
                $message .= "Error trying to delete \"$removedTagName\" from the database. ";
                $class = 'error';
            }
        }
        if ($class != 'success') {
            $message .= "\"$removedTagName\" not removed.";
        }

        $this->Flash->$class($message);
        $this->render('/Tags/flash');
    }

    /**
     * Removes entries from the events_tags join table where either the tag or event no longer exists
     *
     * @return void
     */
    public function removeBrokenAssociations()
    {
        $this->EventsTags = TableRegistry::get('EventsTags');
        set_time_limit(120);

        $associations = $this->EventsTags->find()->toArray();
        $tags = $this->Tags->find('list')->toArray();
        $events = $this->Tags->Events->find('list')->toArray();
        foreach ($associations as $ass) {
            // Note missing tags/events for output message
            $tag = $ass->tag_id;
            if (!isset($tags[$tag])) {
                $missingTags[$tag] = true;
            }
            $eve = $ass->event_id;
            if (!isset($events[$eve])) {
                $missingEvents[$eve] = true;
            }

            // Remove broken association
            if (!isset($tags[$tag]) || !isset($events[$eve])) {
                $this->EventsTags->delete($ass);
            }
        }
        $message = '';
        if (!empty($missingTags)) {
            $message .= 'Removed associations with nonexistent tags: ' . implode(', ', array_keys($missingTags)) . ' ';
        }
        if (!empty($missingEvents)) {
            $message .= 'Removed associations with nonexistent events: ' . implode(', ', array_keys($missingEvents)) . ' ';
        }
        if ($message == '') {
            $message = 'No broken associations to remove.';
        }
        $this->Flash->success($message);
        $this->render('/Tags/flash');
    }

    /**
     * Removes all tags in the 'delete' group
     *
     * @return void
     */
    public function emptyDeleteGroup()
    {
        $deleteGroupId = $this->Tags->getDeleteGroupId();
        $children = $this->Tags->find('children', ['for' => $deleteGroupId]);
        foreach ($children as $child) {
            $this->Tags->delete($child);
        }
        $this->Flash->success('Delete group emptied.');
        $this->render('/Tags/flash');
    }

    /**
     * tag editor
     *
     * @param string|null $tagName name of the tag you wish to edit
     * @return Cake\View\Helper\FlashHelper
     */
    public function edit($tagName = null)
    {
        if ($this->request->is('ajax')) {
            $this->viewBuilder()->setLayout('ajax');
        }
        if ($this->request->is('put') || $this->request->is('post')) {
            $this->request->data['name'] = strtolower(trim($this->request->data['name']));
            $this->request->data['parent_id'] = trim($this->request->data['parent_id']);
            if (empty($this->request->data['parent_id'])) {
                $this->request->data['parent_id'] = null;
            }
            $duplicates = $this->Tags->find()
                ->where(['name' => $this->request->data['name']])
                ->toArray();
            $oldTags = [];
            foreach ($duplicates as $duplicate) {
                $oldTags[] = $duplicate;
            }
            if (!empty($duplicates)) {
                $message = 'That tag\'s name cannot be changed to "';
                $message .= $this->request->data['name'];
                $message .= '" because another tag (';
                $message .= print_r($oldTags);
                $message .= ') already has that name. You can, however, merge this tag into that tag.';

                return $this->Flash->error($message);
            }

            // Set flag to recover tag tree if necessary
            $tag = $this->Tags->find()
                ->where(['id' => $this->request->data['id']])
                ->first();
            $previousParentId = $tag->parent_id;
            $newParentId = $this->request->data['parent_id'];
            $recoverTagTree = ($previousParentId != $newParentId);

            $tag = $this->Tags->patchEntity($tag, $this->request->getData());

            if ($this->Tags->save($tag)) {
                if ($recoverTagTree) {
                    $this->Tags->recover();
                }
                $message = 'Tag successfully edited.';
                if ($this->request->data['listed'] && $tag->parent_id == $this->Tags->getUnlistedGroupId()) {
                    $message .= '<br /><strong>This tag is now listed, but is still in the "Unlisted" group. It is recommended that it now be moved out of that group.</strong>';
                }

                return $this->Flash->success($message);
            }

            return $this->Flash->error('There was an error editing that tag.');
        }
        if (!$tagName) {
            return $this->Flash->error('Please try again, but with a tag name provided this time.');
        }
        $result = $this->Tags->find()
            ->where(['name' => $tagName])
            ->first();
        if (empty($result)) {
            return $this->Flash->error("Could not find a tag with the exact tag name \"$tagName\".");
        }
        if (count($result) > 1) {
            $tagIds = [];
            foreach ($result as $tag) {
                $tagIds[] = $tag->id;
            }

            return $this->Flash->error("Tags with the following IDs are named \"$tagName\": " . implode(', ', $tagIds) . '<br />You will need to merge them before editing.');
        }

        $this->request->data = $result;
    }

    /**
     * adding tags
     *
     * @return void
     */
    public function add()
    {
        $this->viewBuilder()->setLayout('ajax');
        if (!$this->request->is('post')) {
            return;
        }
        if (trim($this->request->data['name']) == '') {
            return $this->Flash->error('Tag name is blank.');
        }

        // Determine parent_id
        $rootParentId = $this->request->data['parent_name'] == '' ? $this->Tags->getUnlistedGroupId() : $this->Tags->getIdFromName($this->request->data['parent_name']);
        if (!$rootParentId) {
            return $this->Flash->error("Parent tag \"" . $this->request->data['parent_name'] . "\" not found");
        }

        $class = 'success';
        $message = '';
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
                $class = 'error';
                $message .= "Error with nested tag structure. Looks like there's an extra indent in line $lineNum: \"$name\".<br />";
            }

            // Strip leading/trailing whitespace and hyphens used for indenting
            $name = trim(ltrim($name, '-'));

            // Confirm that the tag name is non-blank and non-redundant
            if (!$name) {
                continue;
            }
            $exists = $this->Tags->find()
                ->where(['name' => $name])
                ->count();
            if ($exists) {
                $class = 'error';
                $message .= "Cannot create the tag \"$name\" because a tag with that name already exists.<br />";
                continue;
            }

            // Add tag to database
            $newTag = $this->Tags->newEntity();
            $newTag->name = $name;
            $newTag->user_id = $this->request->session()->read('Auth.User.id');
            $newTag->parent_id = $parentId;
            $newTag->listed = 0;
            $newTag->selectable = 1;
            if ($this->Tags->save($newTag)) {
                $this->Flash->success("Created tag #{$newTag->id}: $name");
                $parents[$level + 1] = $newTag->id;
                continue;
            }
            $class = 'error';
            $this->Flash->error("Error creating the tag \"$name\"");
        }

        $this->Flash->$class($message);
        $this->render('/Tags/flash');
    }
}
