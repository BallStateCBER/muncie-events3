<?php
namespace App\Model\Table;

use Cake\Network\Exception\InternalErrorException;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;

/**
 * Tags Model
 *
 * @property \App\Model\Table\EventsTable|\Cake\ORM\Association\BelongsToMany $Events
 * @property \Cake\ORM\Association\BelongsTo $ParentTags
 * @property \Cake\ORM\Association\BelongsTo $Users
 * @property \Cake\ORM\Association\HasMany $ChildTags
 * @property \Cake\ORM\Association\BelongsToMany $EventsTags
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 * @mixin \Cake\ORM\Behavior\TreeBehavior
 * @mixin \Search\Model\Behavior\SearchBehavior
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

        $this->Events = TableRegistry::get('Events');
        $this->EventsTags = TableRegistry::get('EventsTags');
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

    /**
     * Returns the ID of the 'delete' tag group for tags to be deleted.
     *
     * @return int
     */
    public function getDeleteGroupId()
    {
        return 1011;
    }

    /**
     * get tag id from name
     *
     * @param string $name of tag we want
     * @return int
     */
    public function getIdFromName($name)
    {
        $result = $this->find()
            ->select('id')
            ->where(['name' => strtolower($name)])
            ->first();
        if (empty($result)) {
            return null;
        }

        return $result->id;
    }

    /**
     * get tag id from slug
     *
     * @param string $slug of tag we want
     * @return int
     */
    public function getIdFromSlug($slug)
    {
        $splitSlug = explode('_', $slug);

        return (int)$splitSlug[0];
    }

    /**
     * Returns an array of the IDs of Tags associated with Events
     *
     * @param string $direction Optional, either 'future' or 'past'
     * @return array
     */
    public function getIdsWithEvents($direction = null)
    {
        $conditions = [];
        if ($direction == 'future') {
            $conditions['event_id'] = $this->Events->getFutureEventIds();
        } elseif ($direction == 'past') {
            $conditions['event_id'] = $this->Events->getPastEventIds();
        }
        $results = $this->EventsTags->find()
            ->select('tag_id')
            ->where($conditions);
        $retval = [];
        foreach ($results as $result) {
            $retval[] = $result->tag_id;
        }

        return $retval;
    }

    /**
     * get indent level in tree of a tag
     *
     * @param string $name of tag
     * @return int $level
     */
    public function getIndentLevel($name)
    {
        $level = 0;
        $len = mb_strlen($name);
        for ($i = 0; $i < $len; $i++) {
            if ($name[$i] == "\t" || $name[$i] == '-') {
                $level++;
                continue;
            }
            break;
        }

        return $level;
    }

    /**
     * find the id of parent tag
     *
     * @param string $parentName for parent tag
     * @return array
     */
    public function getParentIdFromName($parentName)
    {
        $id = $this->find()
            ->select(['id'])
            ->where(['name' => $parentName])
            ->first();

        return $id->id;
    }

    /**
     * look up a tag entity with the tag id
     *
     * @param int $tagId of tag
     * @return mixed
     */
    public function getTagFromId($tagId)
    {
        $result = $this->find()
            ->select()
            ->where(['id' => $tagId])
            ->first();

        if (isset($result)) {
            return $result;
        }

        return false;
    }

    /**
     * Returns the ID of the 'unlisted' tag group that new custom tags automatically go into.
     *
     * @return int
     */
    public function getUnlistedGroupId()
    {
        return 1012;
    }

    /**
     * get tags with upcoming events
     *
     * @param array $filter future events
     * @return array
     */
    public function getUpcoming($filter = [])
    {
        $filter['direction'] = 'future';

        return $this->getWithCounts($filter);
    }

    /**
     * get ids of tags with events
     *
     * @return array $retval
     */
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

    /**
     * getWithCounts method for getting tags with how many events they have
     *
     * @param array $filter future or past
     * @param string $sort by
     * @return array
     */
    public function getWithCounts($filter = [], $sort = 'alpha')
    {
        // Apply filters and find tags
        $conditions = ['Events.published' => 1];
        if (in_array($filter['direction'], ['future', 'past'])) {
            $dateComparison = $filter['direction'] == 'future' ? '>=' : '<';
            $conditions["Events.start $dateComparison"] = date('Y-m-d H:i:s');
        }
        if (isset($filter['categories'])) {
            $conditions['Events.category_id'] = $filter['categories'];
        }

        $results = $this->Events->find()
            ->select('id')
            ->where($conditions)
            ->contain('Tags')
            ->enableHydration(false);

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

    /**
     * Checks if a tag is under the unlisted group.
     *
     * @param int|null $id of the tag
     * @return bool
     */
    public function isUnderUnlistedGroup($id = null)
    {
        if (!$id) {
            if (!$this['id']) {
                throw new InternalErrorException('Required tag ID not supplied to Tag::isUnderUnlistedGroup().');
            }
            $id = $this['id'];
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
