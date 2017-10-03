<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
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
        $this->addBehavior('Search.Search');

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
            'dependent' => true,
            'cascadeCallbacks' => true,
            'foreignKey' => 'event_id',
            'targetForeignKey' => 'image_id',
            'joinTable' => 'events_images'
        ]);
        $this->belongsToMany('Tags', [
            'dependent' => true,
            'cascadeCallbacks' => true,
            'foreignKey' => 'event_id',
            'targetForeignKey' => 'tag_id',
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
                'field' => ['title', 'description', 'location']
            ])
            ->add('foo', 'Search.Callback');

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
            ->requirePresence('title', 'create')
            ->notEmpty('title');

        $validator
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

    public $deleteSeries = null;

    public $findMethods = [
        'upcomingWithTag' => true,
        'pastWithTag' => true
    ];

    /**
     * buildRules method
     *
     * @param RulesChecker $rules for Event entity
     * @return RulesChecker $rules
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->existsIn(['user_id'], 'Users'));
        $rules->add($rules->existsIn(['category_id'], 'Categories'));
        $rules->add($rules->existsIn(['series_id'], 'EventSeries'));

        return $rules;
    }

    /**
     * getEventsOnDay method
     *
     * @param string $year of Events
     * @param string $month of Events
     * @param string $day of Events
     * @return ResultSet $events
     */
    public function getEventsOnDay($year, $month, $day)
    {
        $date = "$year-$month-$day";
        $events = $this
            ->find('all', [
            'conditions' => ['date' => $date],
            'contain' => ['Users', 'Categories', 'EventSeries', 'Images', 'Tags'],
            'order' => ['date' => 'DESC']
            ])
            ->toArray();

        return $events;
    }

    /**
     * getUpcomingEvents method
     *
     * @return ResultSet $events
     */
    public function getUpcomingEvents()
    {
        $events = $this
            ->find('all', [
            'contain' => ['Users', 'Categories', 'EventSeries', 'Images', 'Tags'],
            'order' => ['date' => 'ASC']
            ])
            ->where(['date >=' => date('Y-m-d')])
            ->toArray();

        return $events;
    }

    /**
     * getUpcomingFilteredEvents method
     *
     * @param array $options for filtering events
     * @return ResultSet $events
     */
    public function getUpcomingFilteredEvents($options)
    {
        $params = [];
        $params[] = ['date >=' => date('Y-m-d')];
        foreach ($options as $param => $value) {
            $categories = '';
            if ($param == 'category') {
                $cats = explode(',', $value);
                foreach ($cats as $cat) {
                    $categories .= "category_id = $cat OR ";
                }
                $categories = substr($categories, 0, -4);
                $categories = '(' . $categories;
                $categories .= ')';
                $params[] = $categories;
            }

            if ($param == 'location') {
                $params[] = ['location' => $value];
            }

            if ($param == 'tags_included') {
                $tagsIncluded = '';
                $tags = explode(',', $value);
                foreach ($tags as $tagName) {
                    $tag = $this->Tags->find()
                        ->where(['name' => $tagName])
                        ->first();

                    $eventTags = $this->EventsTags->find()
                        ->where(['tag_id' => $tag['id']]);

                    foreach ($eventTags as $eventTag) {
                        if (!$eventTag) {
                            continue;
                        }
                        $tagsIncluded .= "Events.id = $eventTag->event_id OR ";
                    }
                }
                $tagsIncluded = substr($tagsIncluded, 0, -4);
                $tagsIncluded = '(' . $tagsIncluded;
                $tagsIncluded .= ')';
                if ($tagsIncluded == '()') {
                    continue;
                }

                $params[] = $tagsIncluded;
            }

            if ($param == 'tags_excluded') {
                $tagsExcluded = '';
                $tags = explode(',', $value);
                foreach ($tags as $tagName) {
                    $tag = $this->Tags->find()
                        ->where(['name' => $tagName])
                        ->first();

                    $eventTags = $this->EventsTags->find()
                        ->where(['tag_id' => $tag['id']]);

                    foreach ($eventTags as $eventTag) {
                        $tagsExcluded .= "Events.id != $eventTag->event_id AND ";
                    }
                }
                $tagsExcluded = substr($tagsExcluded, 0, -4);
                $tagsExcluded = '(' . $tagsExcluded;
                $tagsExcluded .= ')';
                if ($tagsExcluded == '()') {
                    continue;
                }

                $params[] = $tagsExcluded;
            }
        }
        $events = $this->find('all', [
            'contain' => ['Users', 'Categories', 'EventSeries', 'Images', 'Tags'],
            'conditions' => $params
        ])->order(['date' => 'ASC'])->toArray();

        return $events;
    }

    /**
     * getUnapproved method
     *
     * @return int $total
     */
    public function getUnapproved()
    {
        $count = $this->find();
        $count
            ->select()
            ->where(['Events.approved_by IS' => null])
            ->order(['Events.created' => 'asc']);
        $total = $count->count();

        return $total;
    }

    /**
     * getNextStartDate method
     *
     * @param array $dates of events
     * @return string $lastDate
     */
    public function getNextStartDate($dates)
    {
        $lastDate = end($dates);
        $lastDate = strtotime($lastDate . ' +1 day');
        $lastDate = date('Y-m-d', $lastDate);
        list($year, $month, $day) = explode('-', $lastDate);
        $lastDate = $year . $month . $day;

        return $lastDate;
    }

    /**
     * getPrevStartDate method
     *
     * @param array $dates of events
     * @return string $firstDate
     */
    public function getPrevStartDate($dates)
    {
        $firstDate = current($dates);
        $firstDate = strtotime($firstDate . ' -1 day');
        $firstDate = date('Y-m-d', $firstDate);
        list($year, $month, $day) = explode('-', $firstDate);
        $firstDate = $year . $month . $day;

        return $firstDate;
    }

    /**
     * getLocations method
     *
     * @return array $retval
     */
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

    /**
     * getPastLocations method
     *
     * @return array $retval
     */
    public function getPastLocations()
    {
        $locations = $this->find();
        $locations
            ->select(['location'])
            ->where(['date <' => date('Y-m-d')])
            ->group(['location'])
            ->toArray();
        foreach ($locations as $location) {
            $retval[] = $location->location;
        }

        return $retval;
    }

    /**
     * getAllUpcomingEventCounts method
     *
     * @return array $retval
     */
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

    /**
     * getCountInDirectionWithTag method
     *
     * @param string $direction of events
     * @param int $tagId of tag we need events from
     * @return int
     */
    public function getCountInDirectionWithTag($direction, $tagId)
    {
        $conditions = ['tag_id' => $tagId];
        if ($direction == 'future') {
            $conditions['event_id IN'] = $this->getFutureEventIDs();
        }
        if ($direction == 'past') {
            // Since there are always more past events than future, this is quicker
            // than pulling the IDs of all past events
            $conditions['event_id NOT IN'] = $this->getFutureEventIDs();
        }

        return $this->EventsTags->find('all', ['conditions' => $conditions])->count();
    }

    /**
     * getCountPastWithTag method
     *
     * @param int $tagId id of tag-in-question
     * @return int
     */
    public function getCountPastWithTag($tagId)
    {
        return $this->getCountInDirectionWithTag('past', $tagId);
    }

    /**
     * getCountUpcomingWithTag method
     *
     * @param int $tagId id of tag-in-question
     * @return int
     */
    public function getCountUpcomingWithTag($tagId)
    {
        return $this->getCountInDirectionWithTag('future', $tagId);
    }

    /**
     * getPastEventIds method
     *
     * @return array $retval
     */
    public function getPastEventIds()
    {
        $results = $this->find()
            ->select('id')
            ->where(['Events.date <' => date('Y-m-d')])
            ->toArray();
        $retval = [];
        foreach ($results as $result) {
            $retval[] = (int)$result->id;
        }

        return $retval;
    }

    /**
     * Returns the IDs of all events taking place today and in the future
     *
     * @return array
     */
    public function getFutureEventIds()
    {
        $results = $this->find()
            ->select('id')
            ->where(['Events.date >=' => date('Y-m-d')])
            ->toArray();
        $retval = [];
        foreach ($results as $result) {
            $retval[] = (int)$result->id;
        }

        return $retval;
    }

    /**
     * getFutureEvents method
     *
     * @return array $evDates
     */
    public function getFutureEvents()
    {
        $results = $this->find()
            ->select('Events.date')
            ->distinct('Events.date')
            ->where(['Events.date >=' => date('Y-m-d')])
            ->toArray();
        $events = [];
        foreach ($results as $result) {
            $events[] = $result->date;
        }
        foreach ($events as $event) {
            $evDates[] = [$event->format('l'), $event->format('M'), $event->format('m'), $event->format('d'), $event->format('Y')];
        }

        return $evDates;
    }

    /**
     * getIdsFromTag method
     *
     * @param int $tagId id of tag-in-question
     * @return ResultSet $eventId
     */
    public function getIdsFromTag($tagId)
    {
        $eventId = $this->EventsTags->find();
        $eventId
            ->select('event_id')
            ->where(['tag_id' => $tagId])
            ->toArray();

        return $eventId;
    }

    /**
     * Returns an array of dates (YYYY-MM-DD) with published events
     *
     * @param string $month Optional, zero-padded
     * @param int $year Optional
     * @param array $filters Optional
     * @return array
     */
    public function getPopulatedDates($month = null, $year = null, $filters = null)
    {
        $findParams = [
            'conditions' => ['published' => 1],
            'fields' => ['DISTINCT Events.date'],
            'contain' => [],
            'order' => ['date ASC']
        ];

        // Apply optional month/year limits
        if ($month && $year) {
            $month = str_pad($month, 2, '0', STR_PAD_LEFT);
            $findParams['conditions']['Events.date LIKE'] = "$year-$month-%";
            $findParams['limit'] = 31;
        } elseif ($year) {
            $findParams['conditions']['Events.date LIKE'] = "$year-%";
        }

        // Apply optional filters
        if ($filters) {
            $startDate = null;
            $findParams = $this->applyFiltersToFindParams($findParams, $filters, $startDate);
        }

        $dateResults = $this->find('all', $findParams)->toArray();
        $dates = [];
        foreach ($dateResults as $result) {
            if (isset($result['DISTINCT Events']['date'])) {
                $dates[] = $result['DISTINCT Events']['date'];
            }
        }

        return $dates;
    }

    /**
     * getValidFilters method
     *
     * @param array $options filter options
     * @return array $filters
     */
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
            $expectedArrays = ['category_id', 'tags_included', 'tags_excluded'];
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
        $filterTypes = ['category_id', 'location', 'tags_included', 'tags_excluded'];
        foreach ($filterTypes as $type) {
            if (isset($options[$type])) {
                $filters[$type] = $options[$type];
            }
        }

        // Remove categories filter if it specifies all categories
        if (isset($filters['category_id'])) {
            sort($filters['category_id']);
            $allCategoryIds = [];
            $categories = $this->Categories->getAll();
            foreach ($categories as $category) {
                $allCategoryIds[] .= $category->id;
            }
            $allCategoryIds = array_keys($allCategoryIds);
            $excludedCategories = array_diff($allCategoryIds, $filters['category_id']);
            if (empty($excludedCategories)) {
                unset($filters['category_id']);
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
}
