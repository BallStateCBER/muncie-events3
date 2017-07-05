<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Cake\Validation\Validator;

/**
 * Tags Model
 *
 * @property \Cake\ORM\Association\BelongsTo $ParentTags
 * @property \Cake\ORM\Association\BelongsTo $Users
 * @property \Cake\ORM\Association\HasMany $ChildTags
 * @property \Cake\ORM\Association\BelongsToMany $Events
 *
 * @method \App\Model\Entity\Tag get($primaryKey, $options = [])
 * @method \App\Model\Entity\Tag newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Tag[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Tag|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Tag patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Tag[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Tag findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 * @mixin \Cake\ORM\Behavior\TreeBehavior
 */
class TagsTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->setTable('tags');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
        $this->addBehavior('Tree');
        $this->addBehavior('Search.Search');

        $this->belongsTo('ParentTags', [
            'className' => 'Tags',
            'foreignKey' => 'parent_id'
        ]);
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('ChildTags', [
            'className' => 'Tags',
            'foreignKey' => 'parent_id'
        ]);
        $this->belongsToMany('Events', [
            'foreignKey' => 'tag_id',
            'targetForeignKey' => 'event_id',
            'joinTable' => 'events_tags'
        ]);
        $this->searchManager()
            // Here we will alias the 'q' query param to search the `Articles.title`
            // field and the `Articles.content` field, using a LIKE match, with `%`
            // both before and after.
            ->add('filter', 'Search.Like', [
                'before' => true,
                'after' => true,
                'fieldMode' => 'OR',
                'comparison' => 'LIKE',
                'wildcardAny' => '*',
                'wildcardOne' => '?',
                'field' => ['name']
            ])
            ->add('foo', 'Search.Callback');
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->integer('id')
            ->allowEmpty('id', 'create');

        $validator
            ->allowEmpty('name');

        $validator
            ->boolean('listed')
            ->requirePresence('listed', 'create')
            ->notEmpty('listed');

        $validator
            ->boolean('selectable')
            ->requirePresence('selectable', 'create')
            ->notEmpty('selectable');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->existsIn(['parent_id'], 'ParentTags'));
        $rules->add($rules->existsIn(['user_id'], 'Users'));

        return $rules;
    }

    public function getAllWithCounts($conditions)
    {
        $results = $this->Events->find();
        $results
            ->select('id')
            ->where($conditions)
            ->contain('Tags')
            ->toArray();

        $tags = [];
        foreach ($results as $result) {
            foreach ($result['tags'] as $tag) {
                if (isset($tags[$tag['name']])) {
                    $tags[$tag['name']]['count']++;
                    continue;
                }
                $tags[$tag['name']] = [
                    'id' => $tag['id'],
                    'name' => $tag['name'],
                    'count' => 1
                ];
            }
        }
        ksort($tags);

        return $tags;
    }

    public function getCategoriesWithTags($direction = 'future')
    {
        if ($direction == 'future') {
            $eventIds = $this->Events->getFutureEventIDs();
        } elseif ($direction == 'past') {
            $eventIds = $this->Events->getPastEventIDs();
        }
        $taggedEventIds = $this->EventsTags->find();
        $taggedEventIds
            ->select(['event_id'])
            ->join([
                'table' => 'events',
                'type' => 'LEFT',
                'conditions' => 'events.id = event_id'
            ])
            ->where(['event_id in' => $eventIds]);
        $results = $this->Events->find();
        $results
            ->select(['category_id'])
            ->where(['Events.id in' => $taggedEventIds]);
        $retval = [];
        foreach ($results as $result) {
            $retval[] = $result['category_id'];
        }
        return $retval;
    }

    /**
     * Returns the ID of the 'delete' tag group for tags to be deleted.
     * @return int
     */
    public function getDeleteGroupId()
    {
        return 1011;
    }

    public function getIdFromName($name)
    {
        $result = $this->find()
            ->select('id')
            ->where(['name' => strtolower($name)])
            ->first();
        if (empty($result)) {
            return false;
        }
        return $result->id;
    }

    public function getIdFromSlug($slug)
    {
        $splitSlug = explode('_', $slug);
        return (int) $splitSlug[0];
    }

    public function getIndentLevel($name)
    {
        $level = 0;
        for ($i = 0; $i < strlen($name); $i++) {
            if ($name[$i] == "\t" || $name[$i] == '-') {
                $level++;
                continue;
            }
            break;
        }
        return $level;
    }

    public function getTagFromId($tagId)
    {
        $result = $this->find()
            ->select()
            ->where(['id' => $tagId])
            ->first();
        if (empty($result)) {
            return false;
        }
        return $result;
    }

    /**
     * Returns the ID of the 'unlisted' tag group that new custom tags automatically go into.
     * @return int
     */
    public function getUnlistedGroupId()
    {
        return 1012;
    }

    public function getUpcoming($filter = [])
    {
        $filter['direction'] = 'future';
        return $this->getWithCounts($filter);
    }

    public function getUsedTagIds()
    {
        $this->EventsTags = TableRegistry::get('EventsTags');
        $findOptions = [];

        $results = $this->EventsTags->find('all', $findOptions)
                    ->select(['tag_id'])
                    ->distinct(['tag_id'])
                    ->order(['tag_id' => 'ASC'])
                    ->toArray();
        $retval = [];
        foreach ($results as $result) {
            $retval[] = $result->tag_id;
        }
        return $retval;
    }

    public function getWithCounts($filter = [], $sort = 'alpha')
    {
        // Apply filters and find tags
        $conditions = ['Events.published' => 1];
        if ($filter['direction'] == 'future') {
            $conditions['Events.date >='] = date('Y-m-d');
        } elseif ($filter['direction'] == 'past') {
            $conditions['Events.date <'] = date('Y-m-d');
        }
        if (isset($filter['categories'])) {
            $conditions['Events.category_id'] = $filter['categories'];
        }

        $tags = $this->getAllWithCounts($conditions);
        if (empty($tags)) {
            return [];
        }

        if ($sort == 'alpha') {
            return $tags;
        }

        // Sort by count if $sort is not 'alpha'
        $sortedTags = [];
        foreach ($tags as $tag) {
            $sortedTags[$tag['count']][$tag['name']] = $tag;
        }
        krsort($sortedTags);
        $finalTags = [];
        foreach ($sortedTags as $tags) {
            foreach ($tags as $tag) {
                $finalTags[$tag['name']] = $tag;
            }
        }
        return $finalTags;
    }

    public function isUnderUnlistedGroup($id = null)
    {
        if (!$id) {
            if (!$this->id) {
                throw new InternalErrorException('Required tag ID not supplied to Tag::isUnderUnlistedGroup().');
            }
            $id = $this->id;
        }
        $unlistedGroupId = $this->getUnlistedGroupId();

        // Assume that after 100 levels, a circular path must have been found and exit
        for ($n = 0; $n <= 100; $n++) {
            $tag = $this->get($id);

            // Child of root
            if (empty($tag->parent_id)) {
                return false;
            }

            // Child of 'unlisted'
            if ($tag->parent_id == $unlistedGroupId) {
                return true;
            }

            // Go up a level
            $id = $tag->parent_id;
        }

        return false;
    }
}
