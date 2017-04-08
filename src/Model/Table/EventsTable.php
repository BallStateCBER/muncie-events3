<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Events Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Users
 * @property \Cake\ORM\Association\BelongsTo $Categories
 * @property \Cake\ORM\Association\BelongsTo $EventSeries
 * @property \Cake\ORM\Association\BelongsToMany $Images
 * @property \Cake\ORM\Association\BelongsToMany $Tags
 *
 * @method \App\Model\Entity\Event get($primaryKey, $options = [])
 * @method \App\Model\Entity\Event newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Event[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Event|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Event patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Event[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Event findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class EventsTable extends Table
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

        $this->setTable('events');
        $this->setDisplayField('title');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id'
        ]);
        $this->belongsTo('Categories', [
            'foreignKey' => 'category_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('EventSeries', [
            'foreignKey' => 'series_id'
        ]);
        $this->belongsToMany('Images', [
            'foreignKey' => 'event_id',
            'targetForeignKey' => 'image_id',
            'joinTable' => 'events_images'
        ]);
        $this->belongsToMany('Tags', [
            'foreignKey' => 'event_id',
            'targetForeignKey' => 'tag_id',
            'joinTable' => 'events_tags'
        ]);
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
            ->requirePresence('title', 'create')
            ->notEmpty('title');

        $validator
            ->date('date')
            ->requirePresence('date', 'create')
            ->notEmpty('date');

        $validator
            ->requirePresence('location', 'create')
            ->notEmpty('location');

        $validator
            ->requirePresence('description', 'create')
            ->notEmpty('description');

        $validator
            ->integer('category_id')
            ->requirePresence('category_id')
            ->notEmpty('category_id');

        return $validator;
    }

    public $delete_series = null;

    public $findMethods = [
        'upcomingWithTag' => true,
        'pastWithTag' => true
    ];

    public function getValidFilters($options)
    {
        // Correct formatting of $options
        $correctedOptions = [];
        foreach ($options as $var => $val) {
            if (is_string($val)) {
                $val = trim($val);
            }
            if (stripos($var, 'amp;') === 0) {
                $var = str_replace('amp;', '', $var);
            }

            // Turn specified options into arrays if they're comma-delimited strings
            $expectedArrays = ['category', 'tags_included', 'tags_excluded'];
            if (in_array($var, $expectedArrays) && ! is_array($val)) {
                $val = explode(',', $val);
                $correctedArray = [];
                foreach ($val as $member) {
                    $member = trim($member);
                    if ($member != '') {
                        $correctedArray[] = $member;
                    }
                }
                $val = $correctedArray;
            }

            // Only include if not empty
            /* Note: A value of 0 is a valid Widget parameter elsewhere (e.g. the
             * boolean 'outerBorder'), but not valid for any event filters. */
            if (! empty($val)) {
                $correctedOptions[$var] = $val;
            }
        }
        $options = $correctedOptions;

        // Pull event filters out of options
        $filters = [];
        $filterTypes = ['category', 'location', 'tags_included', 'tags_excluded'];
        foreach ($filterTypes as $type) {
            if (isset($options[$type])) {
                $filters[$type] = $options[$type];
            }
        }

        // Remove categories filter if it specifies all categories
        if (isset($filters['category'])) {
            sort($filters['category']);
            $allCategoryIds = array_keys($this->Categories->find('list', ['order' => 'id ASC']));
            $excludedCategories = array_diff($allCategoryIds, $filters['category']);
            if (empty($excludedCategories)) {
                unset($filters['category']);
            }
        }

        // If a tag is both excluded and included, favor excluding
        if (isset($filters['tags_included']) && isset($filters['tags_excluded'])) {
            foreach ($filters['tags_included'] as $k => $id) {
                if (in_array($id, $filters['tags_excluded'])) {
                    unset($filters['tags_included'][$k]);
                }
            }
            if (empty($filters['tags_included'])) {
                unset($filters['tags_included']);
            }
        }

        return $filters;
    }

    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->existsIn(['user_id'], 'Users'));
        $rules->add($rules->existsIn(['category_id'], 'Categories'));
        $rules->add($rules->existsIn(['series_id'], 'EventSeries'));

        return $rules;
    }

    public function getLocations()
    {
        $locations = $this->find();
        $locations
            ->select(['location'])
            ->where(['date >=' => date('Y-m-d')])
            ->group(['location'])
            ->toArray();
        foreach ($locations as $location) {
            $retval[] = $location->location;
        }

        return $retval;
    }

    public function getAllUpcomingEventCounts()
    {
        $results = $this->find();
        $results
            ->select(['category_id'])
            ->select(['count' => $results->func()->count('id')])
            ->where(['date >=' => date('Y-m-d')])
            ->group(['category_id']);

        $retval = [];
        foreach ($results as $result) {
            $catId = $result->category_id;
            $count = $result->count;
            $retval[$catId] = $count;
        }
        return $retval;
    }
}
