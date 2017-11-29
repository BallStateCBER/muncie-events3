<?php
namespace App\Model\Table;

use App\Model\Entity\Event;
use Cake\I18n\Time;
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
 * @property \Cake\ORM\Association\BelongsToMany $EventsTags
 * @property \Cake\ORM\Association\BelongsToMany $Images
 * @property \Cake\ORM\Association\BelongsToMany $Tags
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 * @mixin \Search\Model\Behavior\SearchBehavior
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
            ->allowEmpty('time_end');

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
        $start = date('Y-m-d H:i:s', strtotime("$date 00:00:00"));
        $end = date('Y-m-d H:i:s', strtotime("$date 24:00:00"));
        $event = $this->find()
            ->where(['start >=' => $start])
            ->andWhere(['start <=' => $end])
            ->andWhere(['series_id' => $seriesId])
            ->first();

        return $event;
    }

    /**
     * getEventsOnDay method
     *
     * @param string $year of Events
     * @param string $month of Events
     * @param string $day of Events
     * @param array $filters of Events
     * @return Event[] $events
     */
    public function getEventsOnDay($year, $month, $day, $filters = null)
    {
        $dst = $this->getDaylightSavings(date('Y-m-d'));
        $today = date('Y-m-d H:i:s', strtotime("$year-$month-$day $dst"));
        $tomorrow = date('Y-m-d H:i:s', strtotime("$year-$month-$day $dst + 1 day"));
        $events = $this
            ->find('all', [
            'conditions' => [
                'start >' => $today,
                'start <' => $tomorrow,
                $filters
            ],
            'contain' => ['Users', 'Categories', 'EventSeries', 'Images', 'Tags'],
            'order' => ['start' => 'DESC']
            ])
            ->toArray();

        return $events;
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
     * getFilteredEvents method
     *
     * @param string $nextStartDate to begin
     * @param string $endDate to end
     * @param array $options for filtering events
     * @return array $events
     */
    public function getFilteredEvents($nextStartDate, $endDate, $options)
    {
        $params = [];
        $params[] = ['start >=' => date('Y-m-d', strtotime($nextStartDate))];
        $params[] = ['start <' => date('Y-m-d', $endDate)];
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
        ])->order(['start' => 'ASC'])->toArray();

        return $events;
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
            'order' => ['start' => 'ASC']
            ])
            ->where(['start >=' => date('Y-m-d H:i:s', strtotime($yearMonth))])
            ->toArray();

        return $events;
    }

    /**
     * getRangeEvents method
     *
     * @param string $nextStartDate to begin
     * @param string $endDate to end
     * @return array $events
     */
    public function getRangeEvents($nextStartDate, $endDate)
    {
        $events = $this
            ->find('all', [
            'contain' => ['Users', 'Categories', 'EventSeries', 'Images', 'Tags'],
            'order' => ['start' => 'ASC']
            ])
            ->where(['start >=' => $nextStartDate])
            ->andwhere(['start <=' => $endDate])
            ->toArray();

        return $events;
    }

    /**
     * getStartEndEvents searches for events by range
     *
     * @param string $nextStartDate of range
     * @param string $endDate of range
     * @param array $options in case of filter
     * @return array $events
     */
    public function getStartEndEvents($nextStartDate, $endDate, $options = null)
    {
        $events = $this->getRangeEvents($nextStartDate, $endDate);
        if (empty($events)) {
            $endDate = strtotime($nextStartDate . ' + 4 weeks');
            $events = $options ? $this->getFilteredEvents($nextStartDate, $endDate, $options) : $this->getRangeEvents($nextStartDate, $endDate);
            if (empty($events)) {
                $endDate = strtotime($nextStartDate . ' + 8 weeks');
                $events = $options ? $this->getFilteredEvents($nextStartDate, $endDate, $options) : $this->getRangeEvents($nextStartDate, $endDate);
                if (empty($events)) {
                    $endDate = strtotime($nextStartDate . ' + 16 weeks');
                    $events = $options ? $this->getFilteredEvents($nextStartDate, $endDate, $options) : $this->getRangeEvents($nextStartDate, $endDate);
                    if (empty($events)) {
                        $endDate = strtotime($nextStartDate . ' + 32 weeks');
                        $events = $options ? $this->getFilteredEvents($nextStartDate, $endDate, $options) : $this->getRangeEvents($nextStartDate, $endDate);
                        if (empty($events)) {
                            $endDate = strtotime($nextStartDate . ' + 64 weeks');
                            $events = $options ? $this->getFilteredEvents($nextStartDate, $endDate, $options) : $this->getRangeEvents($nextStartDate, $endDate);
                            if (empty($events)) {
                                $endDate = strtotime($nextStartDate . ' + 128 weeks');
                                $events = $options ? $this->getFilteredEvents($nextStartDate, $endDate, $options) : $this->getRangeEvents($nextStartDate, $endDate);
                            }
                        }
                    }
                }
            }
        }

        return $events;
    }

    /**
     * getUpcomingEvents method
     *
     * @return array $events
     */
    public function getUpcomingEvents()
    {
        $events = $this
            ->find('all', [
            'contain' => ['Users', 'Categories', 'EventSeries', 'Images', 'Tags'],
            'order' => ['start' => 'ASC']
            ])
            ->where(['start >=' => date('Y-m-d H:i:s')])
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
     * getLocations method
     *
     * @return array $retval
     */
    public function getLocations()
    {
        $locations = $this->find();
        $locations
            ->select(['location'])
            ->where(['start >=' => date('Y-m-d')])
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
            ->where(['start <' => date('Y-m-d H:i:s')]);
        $adds = [];
        $locs = [];
        foreach ($locations as $location) {
            $locs[] = $location->location;
            $adds[] = $location->address;
        }
        $retval = array_combine($locs, $adds);
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
            ->where(['start >=' => date('Y-m-d H:i:s')])
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
     * getDaylightSavings method
     *
     * @param string $date date
     * @return string $dst
     */
    public function getDaylightSavings($date)
    {
        $dst = '';
        if (date('I', strtotime($date)) == 1) {
            $dst = ' + 4 hours';
        }
        if (date('I', strtotime($date)) == 0) {
            $dst = ' + 5 hours';
        }

        return $dst;
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
            ->where(['Events.start <' => date('Y-m-d H:i:s')])
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
            ->where(['Events.start >=' => date('Y-m-d H:i:s')])
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
            ->select('Events.start')
            ->distinct('Events.start')
            ->where(['Events.start >=' => date('Y-m-d H:i:s')])
            ->toArray();
        $events = [];
        $evDates = [];
        foreach ($results as $result) {
            $events[] = $result->start;
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
            'fields' => ['DISTINCT Events.start'],
            'contain' => [],
            'order' => ['start ASC']
        ];

        // Apply optional month/year limits
        if ($month && $year) {
            $month = str_pad($month, 2, '0', STR_PAD_LEFT);
            $findParams['conditions']['Events.start LIKE'] = "$year-$month-%";
            $findParams['limit'] = 31;
        } elseif ($year) {
            $findParams['conditions']['Events.start LIKE'] = "$year-%";
        }

        // Apply optional filters
        if ($filters) {
            $findParams = $this->applyFiltersToFindParams($findParams, $filters);
        }

        $dateResults = $this->find('all', $findParams)->toArray();
        $dates = [];
        foreach ($dateResults as $result) {
            if (isset($result['DISTINCT Events']['start'])) {
                $dates[] = substr($result['DISTINCT Events']['start'], 0, -9);
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
     * getDaylightSavings method
     *
     * @param string $date date
     * @return string $dst
     */
    public function setDaylightSavings($date)
    {
        $dst = '';
        if (date('I', strtotime($date)) == 1) {
            $dst = ' - 4 hours';
        }
        if (date('I', strtotime($date)) == 0) {
            $dst = ' - 5 hours';
        }

        return $dst;
    }

    /**
     * setEasternTimes method
     *
     * @param \Cake\Datasource\EntityInterface $event to convert
     * @return \Cake\Datasource\EntityInterface
     */
    public function setEasternTimes($event)
    {
        $start = $event->start->format('Y-m-d H:i:s');
        $dst = $this->setDaylightSavings($start);
        $event->start = new Time(date('Y-m-d H:i:s', strtotime($start . ' ' . $dst)));
        if ($event->end) {
            $end = $event->end->format('Y-m-d H:i:s');
            $dst = $this->setDaylightSavings($end);
            $event->end = new Time(date('Y-m-d H:i:s', strtotime($end . ' ' . $dst)));
        }

        return $event;
    }

    /**
     * setEndUtc method
     *
     * @param string $date to set
     * @param string $end to set
     * @param string $start to compare
     * @return string $retval
     */
    public function setEndUtc($date, $end, $start)
    {
        $dst = $this->getDaylightSavings($date);
        $dateStr = $date . ' ' . $end['hour'] . ':' . $end['minute'] . ' ' . $end['meridian'] . $dst;
        $retval = new Time(date('Y-m-d H:i:s', strtotime($dateStr)));

        if ($end < $start) {
            $retval = new Time(date('Y-m-d H:i:s', strtotime($dateStr . "+1 day")));
        }

        return $retval;
    }

    /**
     * setStartUtc method
     *
     * @param string $date to set
     * @param array $start to set
     * @return string $retval
     */
    public function setStartUtc($date, $start)
    {
        $dst = $this->getDaylightSavings($date);
        $dateStr = $date . ' ' . $start['hour'] . ':' . $start['minute'] . ' ' . $start['meridian'] . $dst;
        $retval = new Time(date('Y-m-d H:i:s', strtotime($dateStr)));

        return $retval;
    }
}
