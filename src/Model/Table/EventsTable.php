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
            ->add('foo', 'Search.Callback', [
                'callback' => function ($query, $args, $filter) {
                    // Modify $query as required
                }
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

    public $deleteSeries = null;

    public $findMethods = [
        'upcomingWithTag' => true,
        'pastWithTag' => true
    ];

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

    public function getNextStartDate($events)
    {
        $eventKeys = array_keys($events);
        $lastDate = end($eventKeys);
        return date('Y-m-d', strtotime("$lastDate + 1 day"));
    }

    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->existsIn(['user_id'], 'Users'));
        $rules->add($rules->existsIn(['category_id'], 'Categories'));
        $rules->add($rules->existsIn(['series_id'], 'EventSeries'));

        return $rules;
    }

    public function arrangeByDate($events)
    {
        $arrangedEvents = [];
        foreach ($events as $event) {
            $date = $event->date;
            $arrangedEvents[$date][] = $event;
        }
        ksort($arrangedEvents);
        return $arrangedEvents;
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

    public function getCountPastWithTag($tagId)
    {
        return $this->getCountInDirectionWithTag('past', $tagId);
    }

    public function getCountUpcomingWithTag($tagId)
    {
        return $this->getCountInDirectionWithTag('future', $tagId);
    }

    public function getPastEventIDs()
    {
        $results = $this->find()
            ->select('id')
            ->where(['Events.date <' => date('Y-m-d')])
            ->toArray();
        $retval = [];
        foreach ($results as $result) {
            $retval[] = (int) $result->id;
        }
        return $retval;
    }

    /**
     * Returns the IDs of all events taking place today and in the future
     * @return array
     */
    public function getFutureEventIDs()
    {
        $results = $this->find()
            ->select('id')
            ->where(['Events.date >=' => date('Y-m-d')])
            ->toArray();
        $retval = [];
        foreach ($results as $result) {
            $retval[] = (int) $result->id;
        }
        return $retval;
    }

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

    public function getIdsFromTag($tagId)
    {
        $eventId = $this->EventsTags->find();
        $eventId
            ->select('event_id')
            ->where(['tag_id' => $tagId])
            ->toArray();
        return $eventId;
    }
}
