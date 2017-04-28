<?php
namespace App\Model\Table;

use Cake\Mailer\Email;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * MailingList Model
 *
 * @property \Cake\ORM\Association\HasMany $Users
 * @property \Cake\ORM\Association\BelongsToMany $Categories
 *
 * @method \App\Model\Entity\MailingList get($primaryKey, $options = [])
 * @method \App\Model\Entity\MailingList newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\MailingList[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\MailingList|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\MailingList patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\MailingList[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\MailingList findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class MailingListTable extends Table
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

        $this->setTable('mailing_list');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->hasMany('Users', [
            'foreignKey' => 'mailing_list_id'
        ]);
        $this->belongsToMany('Categories', [
            'foreignKey' => 'mailing_list_id',
            'targetForeignKey' => 'category_id',
            'joinTable' => 'categories_mailing_list',
            'propertyName' => 'categories'
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
            ->integer('id')
            ->allowEmpty('id', 'create');

        $validator
            ->email('email')
            ->requirePresence('email', 'create')
            ->notEmpty('email');

        $validator
            ->boolean('all_categories')
            ->requirePresence('all_categories', 'create')
            ->notEmpty('all_categories');

        $validator
            ->allowEmpty('categories');

        $validator
            ->boolean('weekly')
            ->requirePresence('weekly', 'create')
            ->notEmpty('weekly');

        $validator
            ->boolean('daily_sun')
            ->requirePresence('daily_sun', 'create')
            ->notEmpty('daily_sun');

        $validator
            ->boolean('daily_mon')
            ->requirePresence('daily_mon', 'create')
            ->notEmpty('daily_mon');

        $validator
            ->boolean('daily_tue')
            ->requirePresence('daily_tue', 'create')
            ->notEmpty('daily_tue');

        $validator
            ->boolean('daily_wed')
            ->requirePresence('daily_wed', 'create')
            ->notEmpty('daily_wed');

        $validator
            ->boolean('daily_thu')
            ->requirePresence('daily_thu', 'create')
            ->notEmpty('daily_thu');

        $validator
            ->boolean('daily_fri')
            ->requirePresence('daily_fri', 'create')
            ->notEmpty('daily_fri');

        $validator
            ->boolean('daily_sat')
            ->requirePresence('daily_sat', 'create')
            ->notEmpty('daily_sat');

        $validator
            ->boolean('new_subscriber')
            ->requirePresence('new_subscriber', 'create')
            ->notEmpty('new_subscriber');

        $validator
            ->dateTime('processed_daily')
            ->allowEmpty('processed_daily');

        $validator
            ->dateTime('processed_weekly')
            ->allowEmpty('processed_weekly');

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
        $rules->add($rules->isUnique(['email']));

        return $rules;
    }

    public function getTodayYMD()
    {
        return [date('Y'), date('m'), date('d')];
    }

    public function getDailyRecipients()
    {
        list($y, $m, $d) = $this->getTodayYMD();
        $conditions = [
        'MailingList.daily_'.strtolower(date('D')) => 1,
        'OR' => [
            'MailingList.processed_daily' => null,
            'MailingList.processed_daily <' => "$y-$m-$d 00:00:00"
        ]
    ];
        if ($this->testing_mode) {
            $conditions['MailingList.id'] = 1;
        }
        return $this->find('all', [
        'conditions' => $conditions,
        'contain' => 'Categories',
        'limit' => 10
    ]);
    }

    public function isWeeklyDeliveryDay()
    {
        return date('l') == 'Thursday';
    }

    public function getWeeklyRecipients()
    {
        list($y, $m, $d) = $this->getTodayYMD();
        $conditions = [
        'MailingList.weekly' => 1,
        'OR' => [
            'MailingList.processed_weekly' => null,
            'MailingList.processed_weekly <' => "$y-$m-$d 00:00:00"
        ]
    ];
        if ($this->testing_mode) {
            $conditions['MailingList.id'] = 1;
        }
        return $this->find('all', [
        'conditions' => $conditions,
        'contain' => 'Categories',
        'limit' => 10
    ]);
    }

    public function getDays()
    {
        return [
            'sun' => 'Sunday',
            'mon' => 'Monday',
            'tue' => 'Tuesday',
            'wed' => 'Wednesday',
            'thu' => 'Thursday',
            'fri' => 'Friday',
            'sat' => 'Saturday'
        ];
    }

    public function getDefaultFormValues($recipient = null)
    {
        $data = [];
        $days = $this->getDays();

        // Settings page: Recipient data provided
        if ($recipient) {
            $daysSelected = 0;
            foreach ($days as $dayAbbrev => $dayName) {
                $daysSelected += $recipient['MailingList']['daily_'.$dayAbbrev];
            }
            if ($recipient['MailingList']['weekly'] && $daysSelected == 0) {
                $data['MailingList']['frequency'] = 'weekly';
            } elseif (! $recipient['MailingList']['weekly'] && $daysSelected == 7) {
                $data['MailingList']['frequency'] = 'daily';
            } else {
                $data['MailingList']['frequency'] = 'custom';
            }
            if ($recipient['MailingList']['all_categories']) {
                $data['MailingList']['event_categories'] = 'all';
            } else {
                $data['MailingList']['event_categories'] = 'custom';
            }
            foreach ($recipient['Categories'] as $category) {
                $data['MailingList']['selected_categories'][$category['id']] = true;
            }
            foreach ($days as $code => $day) {
                $data['MailingList']["daily_$code"] = $recipient['MailingList']["daily_$code"];
            }
            $data['MailingList']['weekly'] = $recipient['MailingList']['weekly'];
            $data['MailingList']['email'] = $recipient['MailingList']['email'];
            if (isset($_GET['unsubscribe'])) {
                $data['unsubscribe'] = 1;
            }

        // Join page: No recipient data
        } else {
            $data['MailingList']['frequency'] = 'weekly';
            $data['MailingList']['event_categories'] = 'all';
            foreach ($days as $code => $day) {
                $data['MailingList']['daily'][$code] = true;
            }
            $categories = $this->Categories->getAll();
            foreach ($categories as $categoryId => $categoryName) {
                $data['MailingList']['selected_categories'][$categoryId] = true;
            }
        }

        return $data;
    }

    public function isNewSubscriber($id)
    {
        return (boolean) $this->field('new_subscriber', ['MailingList.id' => $id]);
    }

    public function getHash($recipient_id)
    {
        return md5('recipient'.$recipient_id);
    }

    public function markDailyAsProcessed($recipient_id, $result)
    {
        $this->MailingListLogTable->save([
            'recipient_id' => $recipient_id,
            'result' => $result,
            'is_daily' => 1
        ]);
        $this->id = $recipient_id;
        return (
            $this->saveField('processed_daily', date('Y-m-d H:i:s')) &&
            $this->saveField('new_subscriber', 0)
        );
    }

    public function markWeeklyAsProcessed($recipient_id, $result)
    {
        $this->MailingListLogTable->save([
            'recipient_id' => $recipient_id,
            'result' => $result,
            'is_weekly' => 1
        ]);
        $this->id = $recipient_id;
        return (
            $this->saveField('processed_weekly', date('Y-m-d H:i:s')) &&
            $this->saveField('new_subscriber', 0)
        );
    }

    public function markAllDailyAsProcessed($recipients, $result)
    {
        foreach ($recipients as $r) {
            $this->markDailyAsProcessed($r['MailingList']['id'], $result);
        }
    }

    public function markAllWeeklyAsProcessed($recipients, $result)
    {
        foreach ($recipients as $r) {
            $this->markWeeklyAsProcessed($r['MailingList']['id'], $result);
        }
    }

    public function filterWeeksEvents($recipient, $events)
    {
        if (! $recipient['MailingList']['all_categories']) {
            $selected_categories = explode(',', $recipient['MailingList']['categories']);
            foreach ($events as $timestamp => $days_events) {
                foreach ($days_events as $k => $event) {
                    if (! in_array($event->Categories->id, $selected_categories)) {
                        unset($events[$timestamp][$k]);
                    }
                }
            }
            foreach ($events as $timestamp => $days_events) {
                if (empty($days_events)) {
                    unset($events[$timestamp]);
                }
            }
        }
        return $events;
    }

    public function filterDaysEvents($recipient, $events)
    {
        if (! $recipient['MailingList']['all_categories']) {
            $selected_categories = explode(',', $recipient['MailingList']['categories']);
            foreach ($events as $k => $event) {
                if (! in_array($event->Categories->id, $selected_categories)) {
                    unset($events[$k]);
                }
            }
        }
        return $events;
    }

    /**
     * A duplication of the TextHelper method with serial comma added
     * @param array $list
     * @param string $and
     * @param string $separator
     * @return string
     */
    public function toList($list, $and = 'and', $separator = ', ')
    {
        if (count($list) > 1) {
            $and = count($list > 2) ? (', ') : (' ');
            $retval = implode($separator, array_slice($list, null, -1));
            $retval .= $and;
            $retval .= array_pop($list);
            return $retval;
        } else {
            return array_pop($list);
        }
    }

    public function getSettingsDisplay($recipient)
    {
        // Categories
        if ($recipient['MailingList']['all_categories']) {
            $event_types = 'All events';
        } else {
            $selected_categories = $recipient['Categories'];
            $category_names = [];
            foreach ($selected_categories as $sc) {
                $category_names[] = $sc['name'];
            }
            $event_types = 'Only '.$this->toList($category_names);
        }

        // Frequency
        $days = $this->getDays();
        $selected_days = [];
        foreach (array_keys($days) as $day) {
            if ($recipient['MailingList']["daily_$day"]) {
                $selected_days[] = $days[$day];
            }
        }
        $day_count = count($selected_days);
        if ($day_count == 7) {
            $frequency = 'Daily';
            if ($recipient['MailingList']['weekly']) {
                $frequency .= ' and weekly';
            }
        } elseif ($day_count > 0) {
            $frequency = 'Daily on '.$this->toList($selected_days);
            if ($recipient['MailingList']['weekly']) {
                $frequency .= ' and weekly';
            }
        } else {
            $frequency = $recipient['MailingList']['weekly'] ? 'Weekly' : '?';
        }

        return compact('event_types', 'frequency');
    }

    /**
     * Returns a welcome message if $recipient_id is not set or
     * corresponds to a user who hasn't received any emails yet,
     * null otherwise.
     * @param int $recipient_id
     * @return NULL|string
     */
    public function getWelcomeMessage($recipient_id = null)
    {
        if ($recipient_id && $this->isNewSubscriber($recipient_id)) {
            $message = 'Thanks for signing up for the Muncie Events ';
            $message .= 'mailing list! Don\'t hesitate to contact us ';
            $message .= '('.Router::url(['controller' => 'pages', 'action' => 'contact'], true).') ';
            $message .= 'if you have any questions, comments, or suggestions. ';
            $message .= 'Remember that at any time, you can adjust your settings and ';
            $message .= 'change how often you receive these emails and what types of ';
            $message .= 'events you\'re interested in hearing about.';
            return $message;
        }
        return null;
    }

    /**
     * Sends the daily version of the event email.
     * @param array $recipient
     * @param array $events
     * @return array:boolean string
     */
    public function sendDaily($recipient, $events, $testing = false)
    {
        $recipient_id = $recipient['MailingList']['id'];

        if ($this->testing_mode && $recipient_id != 1) {
            return [false, 'Email not sent to '.$recipient['MailingList']['email'].' because the mailing list is in testing mode.'];
        }

        // Make sure there are events to begin with
        if (empty($events)) {
            $this->markDailyAsProcessed($recipient_id, 2);
            return [false, 'Email not sent to '.$recipient['MailingList']['email'].' because there are no events to report.'];
        }

        // Eliminate any events that this user isn't interested in
        $events = $this->filterDaysEvents($recipient, $events);

        // Make sure there are events left
        if (empty($events)) {
            $event_categories = [];
            foreach ($events as $k => $event) {
                $event_categories[] = $event->Categories->id;
            }
            $this->markDailyAsProcessed($recipient_id, 3);
            $message = 'No events to report, resulting from '.$recipient['MailingList']['email'].'\'s settings<br />';
            $message .= 'Selected: '.$recipient['MailingList']['categories'].'<br />';
            $message .= 'Available: '.(empty($event_categories) ? 'None' : implode(',', $event_categories));
            return [false, $message];
        }

        // Fake sending an email if testing
        if ($testing) {
            $event_titles = [];
            foreach ($events as $e) {
                $event_titles[] = $e['Event']['title'];
            }
            $message = 'Email would have been sent to '.$recipient['MailingList']['email'].'<br />Events: '.implode('; ', $event_titles);
            return [true, $message];
        }

        // Send real email
        $recipient_id = $recipient['MailingList']['id'];
        $email = new Email('mailing_list');
        $subject = 'Today in Muncie: '.date("l, M j");
        $email
            ->setTo($recipient['MailingList']['email'])
            ->setSubject($subject)
            ->setTemplate('daily')
            ->setEmailFormat('both')
            ->setHelpers(['Html', 'Text'])
            ->viewVars([
                'titleForLayout' => $subject,
                'events' => $events,
                'recipient_email' => $recipient['MailingList']['email'],
                'recipient_id' => $recipient['MailingList']['id'],
                'date' => date("l, F jS, Y"),
                'hash' => $this->getHash($recipient_id),
                'welcome_message' => $this->getWelcomeMessage($recipient_id),
                'settings_display' => $this->getSettingsDisplay($recipient)
            ]);
        if ($email->send()) {
            $this->markDailyAsProcessed($recipient_id, 0);
            return [true, 'Email sent to '.$recipient['MailingList']['email']];
        } else {
            $this->markDailyAsProcessed($recipient_id, 1);
            return [false, 'Error sending email to '.$recipient['MailingList']['email']];
        }
    }

    /**
     * Sends the weekly version of the event email.
     * @param array $recipient
     * @param array $events
     * @return array:boolean string
     */
    public function sendWeekly($recipient, $events, $testing = false)
    {
        $recipient_id = $recipient['MailingList']['id'];

        if ($this->testing_mode && $recipient_id != 1) {
            return [false, 'Email not sent to '.$recipient['MailingList']['email'].' because the mailing list is in testing mode.'];
        }

        // Make sure there are events to begin with
        $events_count = 0;
        foreach ($events as $day => $d_events) {
            $events_count += count($d_events);
        }
        if (! $events_count) {
            $this->markWeeklyAsProcessed($recipient_id, 2);
            return [false, 'Email not sent to '.$recipient['MailingList']['email'].' because there are no events to report.'];
        }

        // Eliminate any events that this user isn't interested in
        $events = $this->filterWeeksEvents($recipient, $events);

        // Make sure there are events left
        if (empty($events)) {
            // No events to report to this user today.
            $event_categories = [];
            foreach ($events as $k => $event) {
                $event_categories[] = $event->Categories->id;
            }
            $this->markWeeklyAsProcessed($recipient_id, 3);
            $message = 'No events to report, resulting from '.$recipient['MailingList']['email'].'\'s settings<br />';
            $message .= 'Selected: '.$recipient['MailingList']['categories'].'<br />';
            $message .= 'Available: '.(empty($event_categories) ? 'None' : implode(',', $event_categories));
            return [false, $message];
        }

        // Fake sending an email if testing
        if ($testing) {
            $event_titles = [];
            foreach ($events as $timestamp => $days_events) {
                foreach ($days_events as $k => $e) {
                    $event_titles[] = $e['Event']['title'];
                }
            }
            $message = 'Email would have been sent to '.$recipient['MailingList']['email'].'<br />Events: '.implode('; ', $event_titles);
            return [true, $message];
        }

        // Send real email
        $recipient_id = $recipient['MailingList']['id'];
        $email = new Email('mailing_list');
        $subject = 'Upcoming Week in Muncie: '.date("M j");
        $email
            ->setTo($recipient['MailingList']['email'])
            ->setSubject($subject)
            ->setTemplate('weekly')
            ->setEmailFormat('both')
            ->setHelpers(['Html', 'Text'])
            ->viewVars([
                'titleForLayout' => $subject,
                'events' => $events,
                'recipient_email' => $recipient['MailingList']['email'],
                'recipient_id' => $recipient['MailingList']['id'],
                'date' => date("l, F jS, Y"),
                'hash' => $this->getHash($recipient_id),
                'welcome_message' => $this->getWelcomeMessage($recipient_id),
                'settings_display' => $this->getSettingsDisplay($recipient)
            ]);
        if ($email->send()) {
            $this->markWeeklyAsProcessed($recipient_id, 0);
            return [true, 'Email sent to '.$recipient['MailingList']['email']];
        } else {
            $this->markWeeklyAsProcessed($recipient_id, 1);
            return [false, 'Error sending email to '.$recipient['MailingList']['email']];
        }
    }
}
