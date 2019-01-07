<?php
namespace App\Model\Table;

use App\Model\Entity\Event;
use Cake\Database\Expression\QueryExpression;
use Cake\Http\Exception\InternalErrorException;
use Cake\I18n\Time;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;

/**
 * Events Model
 *
 * @property \App\Model\Table\UsersTable|\Cake\ORM\Association\BelongsTo $Users
 * @property \App\Model\Table\CategoriesTable|\Cake\ORM\Association\BelongsTo $Categories
 * @property \App\Model\Table\EventSeriesTable|\Cake\ORM\Association\BelongsTo $EventSeries
 * @property \Cake\ORM\Table|\Cake\ORM\Association\HasMany $EventsTags
 * @property \App\Model\Table\ImagesTable|\Cake\ORM\Association\BelongsToMany $Images
 * @property \App\Model\Table\TagsTable|\Cake\ORM\Association\BelongsToMany $Tags
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 * @mixin \Search\Model\Behavior\SearchBehavior
 * @property \Cake\ORM\Table|\Cake\ORM\Association\HasMany $EventsImages
 * @method \App\Model\Entity\Event get($primaryKey, $options = [])
 * @method \App\Model\Entity\Event newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Event[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Event|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Event|bool saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Event patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Event[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Event findOrCreate($search, callable $callback = null, $options = [])
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

        $this->EventsTags = TableRegistry::getTableLocator()->get('EventsTags');
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
            ->minLength('title', 1);

        $validator
            ->date('date')
            ->requirePresence('date', 'create');

        $validator
            ->date('time_end')
            ->allowEmptyDate('time_end');

        $validator
            ->requirePresence('location', 'create')
            ->minLength('location', 1);

        $validator
            ->requirePresence('description', 'create')
            ->minLength('description', 1);

        $validator
            ->integer('category_id')
            ->requirePresence('category_id')
            ->greaterThan('category_id', 0);

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
     * applyFiltersToFindParams method
     *
     * @param array $findParams we need to find
     * @param array $filters the results
     * @return mixed
     */
    public function applyFiltersToFindParams($findParams, $filters = [])
    {
        if (isset($filters['category']) && ! empty($filters['category'])) {
            $findParams['conditions']['category_id'] = $filters['category'];
        }
        if (isset($filters['location']) && ! empty($filters['location'])) {
            $findParams['conditions']['location LIKE'] = '%' . $filters['location'] . '%';
        }

        // If there are included/excluded tags, retrieve all potentially
        // applicable event IDs that must / must not be part of the final results
        $eventIds = [];
        foreach (['included', 'excluded'] as $foocluded) {
            if (isset($filters["tags_$foocluded"])) {
                $results = $this->Tags->find(['contain' => ['Event' => [
                    'fields' => ['id'],
                    'conditions' => $findParams['conditions']
                ]]])
                    ->select(['id'])
                    ->where(['id' => $filters["tags_$foocluded"]])
                    ->toArray();
                $eventIds[$foocluded] = [];
                foreach ($results as $result) {
                    foreach ($result['Event'] as $event) {
                        $eventIds[$foocluded][] = $event['id'];
                    }
                }
            }
        }
        if (isset($eventIds['included'])) {
            $findParams['conditions']['id'] = $eventIds['included'];
        }
        if (isset($eventIds['excluded'])) {
            $findParams['conditions']['id NOT'] = $eventIds['excluded'];
        }

        return $findParams;
    }

    /**
     * Returns the most recently published address
     * for the provided location name or FALSE if none is found
     *
     * @param string $location we need address for
     * @return bool|array
     */
    public function getAddress($location)
    {
        $result = $this->find()
            ->select(['address'])
            ->where(['published' => 1])
            ->andWhere(['location' => $location])
            ->andWhere(['address IS NOT' => ''])
            ->andWhere(['address IS NOT' => null])
            ->first();
        if (empty($result)) {
            return false;
        }

        return $result['address'];
    }

    /**
     * getEventByDateAndSeries method
     *
     * @param string $date Date string in 'YYYY-MM-DD' format
     * @param string $seriesId Event series ID
     * @return Event $event
     */
    public function getEventsByDateAndSeries($date, $seriesId)
    {
        /** @var Event $event */
        $event = $this->find()
            ->where(['date >=' => $date])
            ->andWhere(['date <=' => $date])
            ->andWhere(['series_id' => $seriesId])
            ->first();

        return $event;
    }

    /**
     * Returns an array of published events taking place on the specified day
     *
     * @param string $year A four-digit year
     * @param string $month A two-digit month
     * @param string $day A two-digit day
     * @param array $conditions Additional conditions to apply to query
     * @return Event[]
     */
    public function getEventsOnDay($year, $month, $day, $conditions = [])
    {
        $defaultConditions = [
            'date' => "$year-$month-$day",
            'Events.published' => 1
        ];
        $conditions = array_merge($defaultConditions, $conditions);

        return $this
            ->find('all', [
                'conditions' => $conditions,
                'contain' => ['Users', 'Categories', 'EventSeries', 'Images', 'Tags'],
                'order' => ['time_start' => 'ASC']
            ])
            ->toArray();
    }

    /**
     * getEventsUpcomingWeek method
     *
     * @param int $y year
     * @param int $m month
     * @param int $d day
     * @param bool $onlyApproved or not
     * @return array
     */
    public function getEventsUpcomingWeek($y, $m, $d, $onlyApproved)
    {
        $events = [];
        for ($n = 0; $n < 7; $n++) {
            $timestamp = mktime(0, 0, 0, $m, ($d + $n), $y);
            $thisY = date('Y', $timestamp);
            $thisM = date('m', $timestamp);
            $thisD = date('d', $timestamp);
            $daysEvents = $this->getEventsOnDay($thisY, $thisM, $thisD, $onlyApproved);
            if (! empty($daysEvents)) {
                $events[$timestamp] = $daysEvents;
            }
        }

        return $events;
    }

    /**
     * Retrieves events from the database within the specified dates and modified by $options
     *
     * @param string $startDate Earliest date for returned events (YYYY-MM-DD)
     * @param string $endDate Latest date for returned events (YYYY-MM-DD)
     * @param array $options Filters and other options
     * @return array
     */
    public function getFilteredEvents($startDate, $endDate, $options = [])
    {
        $query = $this
            ->find('all')
            ->contain([
                'Users',
                'Categories',
                'EventSeries',
                'Images',
                'Tags'
            ])
            ->order([
                'date' => 'ASC',
                'time_start' => 'ASC'
            ])
            ->where([
                'Events.date >=' => $startDate,
                'Events.date <=' => $endDate,
                'Events.published' => 1
            ]);

        if (isset($options['category_id'])) {
            $query->find('inCategory', [
                'category_id' => $options['category_id']
            ]);
        }

        if (isset($options['location'])) {
            $query->where(['location' => $options['location']]);
        }

        if (isset($options['tags_included'])) {
            $query->find('withTagsByName', [
                'tag_names' => $options['tags_included']
            ]);
        }

        if (isset($options['tags_excluded'])) {
            $query->find('withoutTagsByName', [
                'tag_names' => $options['tags_excluded']
            ]);
        }

        return $query->toArray();
    }

    /**
     * getMonthEvents method
     *
     * @param string $yearMonth of events
     * @return array $events
     */
    public function getMonthEvents($yearMonth)
    {
        $events = $this
            ->find('all', [
                'contain' => ['Users', 'Categories', 'EventSeries', 'Images', 'Tags'],
                'order' => [
                    'date' => 'ASC',
                    'time_start' => 'ASC'
                ]
            ])
            ->where(['date >=' => date('Y-m-d', strtotime($yearMonth))])
            ->toArray();

        return $events;
    }

    /**
     * Returns an array of no fewer than seven events within 128 weeks of $endDate if possible, or fewer than seven
     * events if no more could be found within 128 weeks of $endDate
     *
     * @param string $nextStartDate The date or datetime string immediately before the earliest date of results if
     *     $options is empty, or the earliest date of the results if $options is not
     * @param string $endDate The date or datettime string of the last day of results, or the day after the last day
     *     of results if $options is not empty
     * @param array $options An array of filter keys and values, such as 'location' => 'Location Name'
     * @return array $events
     */
    public function getStartEndEvents($nextStartDate, $endDate, $options = [])
    {
        $events = $this->getFilteredEvents($nextStartDate, $endDate, $options);

        for ($i = 2; $i <= 7; $i++) {
            if (count($events) < 7) {
                break;
            }
            $weeks = pow(2, $i);
            $endDate = strtotime($nextStartDate . ' + ' . $weeks . ' weeks');
            $events = $this->getFilteredEvents($nextStartDate, $endDate, $options);
        }

        return $events;
    }

    /**
     * Returns events happening on and after today
     *
     * @return array
     */
    public function getUpcomingEvents()
    {
        $events = $this
            ->find('all', [
                'contain' => ['Users', 'Categories', 'EventSeries', 'Images', 'Tags'],
                'order' => [
                    'date' => 'ASC',
                    'time_start' => 'ASC'
                ]
            ])
            ->where(['date >=' => date('Y-m-d')])
            ->toArray();

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
     * getLocationFromSlug method
     *
     * @param string $slug for location
     * @return string
     */
    public function getLocationFromSlug($slug)
    {
        $location = $this->find()
            ->select(['location'])
            ->where(['location_slug' => $slug])
            ->first();

        return $location['location'];
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
            ->order(['date' => 'ASC'])
            ->group(['location'])
            ->toArray();
        $retval = [];
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
            ->select(['location', 'address'])
            ->where(['date <' => date('Y-m-d')]);
        $adds = [];
        $locs = [];
        foreach ($locations as $location) {
            $locs[] = trim($location->location);
            $adds[] = $location->address;
        }
        $retval = array_combine($locs, $adds);
        ksort($retval);

        return $retval;
    }

    /**
     * getPastLocationsWithSlugs method
     *
     * @return array $retval
     */
    public function getPastLocationsWithSlugs()
    {
        $locations = $this->find();
        $locations
            ->select(['location', 'location_slug'])
            ->where(['date <' => date('Y-m-d')]);
        $slugs = [];
        $locs = [];
        foreach ($locations as $location) {
            $locs[] = trim($location->location);
            $slugs[] = $location->location_slug;
        }
        $retval = array_combine($locs, $slugs);
        ksort($retval);

        return $retval;
    }

    /**
     * getUpcomingLocationsWithSlugs method
     *
     * @return array $retval
     */
    public function getUpcomingLocationsWithSlugs()
    {
        $locations = $this->find();
        $locations
            ->select(['location', 'location_slug'])
            ->where(['date >=' => date('Y-m-d')]);
        $slugs = [];
        $locs = [];
        foreach ($locations as $location) {
            $locs[] = $location->location;
            $slugs[] = $location->location_slug;
        }
        $retval = array_combine($locs, $slugs);
        ksort($retval);

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
            $conditions['event_id IN'] = $this->getFutureEventIds();
        }
        if ($direction == 'past') {
            // Since there are always more past events than future, this is quicker
            // than pulling the IDs of all past events
            $conditions['event_id NOT IN'] = $this->getFutureEventIds();
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
     * Returns a positive UTC offset for Muncie's timezone for the provided date
     *
     * @param string $date A strtotime() parsable date string
     * @return string
     */
    public function getDaylightSavingOffsetPositive($date)
    {
        if (date('I', strtotime($date)) == 1) {
            return ' + 4 hours';
        }

        return ' + 5 hours';
    }

    /**
     * Returns a negative UTC offset for Muncie's timezone for the provided date
     *
     * @param string $date A strtotime() parsable date string
     * @return string
     */
    public function getDaylightSavingOffsetNegative($date)
    {
        $offset = $this->getDaylightSavingOffsetPositive($date);

        return str_replace('+', '-', $offset);
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
            ->where(['Events.time_start <' => date('H:i:s')])
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
            ->where(['Events.time_start >=' => date('H:i:s')])
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
     * @return array
     */
    public function getFuturePopulatedDates()
    {
        $results = $this->find()
            ->select('Events.date')
            ->distinct('Events.date')
            ->where(['Events.date >=' => date('H:i:s')])
            ->toArray();
        $evDates = [];
        foreach ($results as $result) {
            $evDates[] = [
                $result->time_start->format('l'),
                $result->time_start->format('M'),
                $result->time_start->format('m'),
                $result->time_start->format('d'),
                $result->time_start->format('Y')
            ];
        }

        return $evDates;
    }

    /**
     * getIdsFromTag method
     *
     * @param int $tagId id of tag-in-question
     * @return array $eventId
     */
    public function getIdsFromTag($tagId)
    {
        $eventId = $this->EventsTags->find()
            ->select('event_id')
            ->where(['tag_id' => $tagId])
            ->toArray();
        $retval = [];
        foreach ($eventId as $id) {
            $retval[] = $id->event_id;
        }

        return $retval;
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
            'order' => ['Events.date ASC']
        ];

        // Apply optional month/year limits
        if ($month && $year) {
            $month = str_pad($month, 2, '0', STR_PAD_LEFT);
            $findParams['conditions']['Events.time_start LIKE'] = "$year-$month-%";
            $findParams['limit'] = 31;
        } elseif ($year) {
            $findParams['conditions']['Events.time_start LIKE'] = "$year-%";
        }

        // Apply optional filters
        if ($filters) {
            $findParams = $this->applyFiltersToFindParams($findParams, $filters);
        }

        $dateResults = $this->find('all', $findParams)->toArray();
        $dates = [];
        foreach ($dateResults as $result) {
            $dates[] = date('Y-m-d', strtotime($result['DISTINCT Events']['date']));
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
            if (mb_stripos($var, 'amp;') === 0) {
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
            $categories = $this->Categories->find('list')->toArray();
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

    /**
     * Limits a query to only events matching the specified category (or categories)
     *
     * @param Query $query Query object
     * @param array $options Array including 'category_id' key
     * @return Query
     * @throws InternalErrorException
     */
    public function findInCategory(Query $query, array $options)
    {
        if (!isset($options['category_id'])) {
            throw new InternalErrorException('category_id parameter is missing');
        }
        $categoryIds = is_array($options['category_id']) ? $options['category_id'] : [$options['category_id']];

        return $query->where(function (QueryExpression $exp) use ($categoryIds) {
            return $exp->in('category_id', $categoryIds);
        });
    }

    /**
     * Modifies a query to return only events that match the tags specified by name
     *
     * @param Query $query Query object
     * @param array $options Options array, necessarily containing a 'tag_names' key
     * @return Query
     * @throws InternalErrorException
     */
    public function findWithTagsByName(Query $query, array $options)
    {
        if (!isset($options['tag_names'])) {
            throw new InternalErrorException('tag_names parameter is missing');
        }

        $tagNames = $options['tag_names'];

        return $query->matching('Tags', function (Query $q) use ($tagNames) {
            return $q->where(function (QueryExpression $exp) use ($tagNames) {
                return $exp->in('Tags.name', $tagNames);
            });
        });
    }

    /**
     * Modifies a query to return only events that do NOT match the tags specified by name
     *
     * @param Query $query Query object
     * @param array $options Options array, necessarily containing a 'tag_names' key
     * @return Query
     * @throws InternalErrorException
     */
    public function findWithoutTagsByName(Query $query, array $options)
    {
        if (!isset($options['tag_names'])) {
            throw new InternalErrorException('tag_names parameter is missing');
        }

        $tagNames = $options['tag_names'];

        return $query->notMatching('Tags', function (Query $q) use ($tagNames) {
            return $q->where(function (QueryExpression $exp) use ($tagNames) {
                return $exp->in('Tags.name', $tagNames);
            });
        });
    }
}
