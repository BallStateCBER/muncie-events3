<?php
namespace App\Model\Table;

use Cake\Mailer\Email;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Cake\Validation\Validator;

/**
 * MailingList Model
 *
 * @property \App\Model\Table\UsersTable|\Cake\ORM\Association\HasMany $Users
 * @property \Cake\ORM\Association\BelongsTo $MailingListLog
 * @property \App\Model\Table\CategoriesTable|\Cake\ORM\Association\BelongsToMany $Categories
 * @property \Cake\ORM\Table|\Cake\ORM\Association\HasMany $CategoriesMailingList
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 * @method \App\Model\Entity\MailingList get($primaryKey, $options = [])
 * @method \App\Model\Entity\MailingList newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\MailingList[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\MailingList|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\MailingList|bool saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\MailingList patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\MailingList[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\MailingList findOrCreate($search, callable $callback = null, $options = [])
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

        $this->CategoriesMailingList = TableRegistry::getTableLocator()->get('CategoriesMailingList');
        $this->MailingListLog = TableRegistry::getTableLocator()->get('MailingListLog');
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
            ->requirePresence('id', 'update');

        $validator
            ->email('email')
            ->requirePresence('email', 'create')
            ->minLength('email', 5);

        $validator
            ->boolean('all_categories')
            ->requirePresence('all_categories', 'create');

        $validator
            ->boolean('weekly')
            ->requirePresence('weekly', 'create');

        $validator
            ->boolean('daily_sun')
            ->requirePresence('daily_sun', 'create');

        $validator
            ->boolean('daily_mon')
            ->requirePresence('daily_mon', 'create');

        $validator
            ->boolean('daily_tue')
            ->requirePresence('daily_tue', 'create');

        $validator
            ->boolean('daily_wed')
            ->requirePresence('daily_wed', 'create');

        $validator
            ->boolean('daily_thu')
            ->requirePresence('daily_thu', 'create');

        $validator
            ->boolean('daily_fri')
            ->requirePresence('daily_fri', 'create');

        $validator
            ->boolean('daily_sat')
            ->requirePresence('daily_sat', 'create');

        $validator
            ->boolean('new_subscriber')
            ->requirePresence('new_subscriber', 'create');

        $validator
            ->dateTime('processed_daily');

        $validator
            ->dateTime('processed_weekly');

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

    /**
     * an array version of today's date
     *
     * @return array
     */
    public function getTodayYMD()
    {
        return [date('Y'), date('m'), date('d')];
    }

    /**
     * getDailyRecipients method.
     *
     * @return array $retval
     */
    public function getDailyRecipients()
    {
        list($year, $mon, $day) = $this->getTodayYMD();
        $conditions = [
        'MailingList.daily_' . strtolower(date('D')) => 1,
        'OR' => [
            'MailingList.processed_daily' => null,
            'MailingList.processed_daily <' => "$year-$mon-$day 00:00:00"
            ]
        ];

        $retval = $this->find()
                    ->where([$conditions])
                    ->contain(['Categories'])
                    ->limit(10)
                    ->toArray();

        return $retval;
    }

    /**
     * gives the day that the weekly mailing list is sent out.
     *
     * @return bool
     */
    public function getWeeklyDeliveryDay()
    {
        if (date('l') == 'Thursday') {
            return true;
        }

        return false;
    }

    /**
     * getWeeklyRecipients method.
     *
     * @return array
     */
    public function getWeeklyRecipients()
    {
        list($year, $mon, $day) = $this->getTodayYMD();
        $conditions = [
        'MailingList.weekly' => 1,
        'OR' => [
            'MailingList.processed_weekly' => null,
            'MailingList.processed_weekly <' => "$year-$mon-$day 00:00:00"
            ]
        ];

        $retval = $this->find()
            ->where([$conditions])
            ->contain(['Categories'])
            ->limit(10)
            ->toArray();

        return $retval;
    }

    /**
     * these are the days of the week.
     *
     * @return array
     */
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

    /**
     * Determined the values that MailingListController->request->data should be prepopulated with for the 'join' and 'settings' pages
     *
     * @param array|null $recipient data provided
     * @return array
     */
    public function getDefaultFormValues($recipient = null)
    {
        $data = [];
        $days = $this->getDays();

        // Settings page: Recipient data provided
        if ($recipient) {
            $daysSelected = 0;
            foreach ($days as $dayAbbrev => $dayName) {
                $daysSelected .= $recipient["daily_$dayAbbrev"];
            }
            if ($recipient['weekly'] && $daysSelected == 0) {
                $data['frequency'] = 'weekly';
            }
            if (! $recipient['weekly'] && $daysSelected == 7) {
                $data['frequency'] = 'daily';
            }
            if (! $recipient['weekly'] && $daysSelected != 0 || $daysSelected != 7) {
                $data['frequency'] = 'custom';
            }
            $data['event_categories'] = $recipient['all_categories'] ? 'all' : 'custom';
            $categories = $this->CategoriesMailingList->find()
                ->where(['mailing_list_id' => $recipient['id']]);
            if ($categories) {
                foreach ($categories as $category) {
                    $data['selected_categories'][$category['id']] = true;
                }
            }
            foreach ($days as $code => $day) {
                $data["daily_$code"] = $recipient["daily_$code"];
            }
            $data['weekly'] = $recipient['weekly'];
            $data['email'] = $recipient['email'];
            if (isset(filter_input_array(INPUT_GET)['unsubscribe'])) {
                $data['unsubscribe'] = 1;
            }
        }
        // Join page: No recipient data
        if (!$recipient) {
            $data['frequency'] = 'weekly';
            $data['event_categories'] = 'all';
            foreach ($days as $code => $day) {
                $data["daily_$code"] = true;
            }
            $categories = $this->Categories->find('list')->toArray();
            foreach ($categories as $categoryId => $categoryName) {
                $data['selected_categories'][$categoryId] = true;
            }
        }

        return $data;
    }

    /**
     * isNewSubscriber method.
     *
     * @param int $id for recipient
     * @return bool
     */
    public function isNewSubscriber($id)
    {
        $subscriber = $this->get($id);

        return (bool)$subscriber->new_subscriber;
    }

    /**
     * isNewSubscriberEmail method.
     *
     * @param string $email for recipient
     * @return bool
     */
    public function isNewSubscriberEmail($email)
    {
        $subscriber = $this->find()
            ->where(['email' => $email])
            ->first();

        return (bool)!$subscriber;
    }

    /**
     * getHash method.
     *
     * @param int $recipientId for recipient
     * @return string
     */
    public function getHash($recipientId)
    {
        return md5('recipient' . $recipientId);
    }

    /**
     * setDailyAsProcessed method.
     *
     * @param int $recipientId for recipient
     * @param string $result of process
     * @return null
     */
    public function setDailyAsProcessed($recipientId, $result)
    {
        $processed = $this->MailingListLog->newEntity();
        $processed->recipient_id = $recipientId;
        $processed->result = $result;
        $processed->is_daily = 1;
        $processed->created = date('Y-m-d H:i:s');
        if (php_sapi_name() == 'cli') {
            $processed->testing = 1;
        }
        $this->MailingListLog->save($processed);

        $recipient = $this->get($recipientId);
        $recipient->processed_daily = date('Y-m-d H:i:s');
        $recipient->new_subscriber = 0;

        $this->save($recipient);

        return null;
    }

    /**
     * setWeeklyAsProcessed method.
     *
     * @param int $recipientId for recipient
     * @param string $result of process
     * @return null
     */
    public function setWeeklyAsProcessed($recipientId, $result)
    {
        $processed = $this->MailingListLog->newEntity();
        $processed->recipient_id = $recipientId;
        $processed->result = $result;
        $processed->is_weekly = 1;
        $processed->created = date('Y-m-d H:i:s');
        if (php_sapi_name() == 'cli') {
            $processed->testing = 1;
        }
        $this->MailingListLog->save($processed);

        $recipient = $this->get($recipientId);
        $recipient->processed_weekly = date('Y-m-d H:i:s');
        $recipient->new_subscriber = 0;

        $this->save($recipient);

        return null;
    }

    /**
     * setAllDailyAsProcessed method.
     *
     * @param array $recipients settings for recipients
     * @param string $result of processing
     * @return bool|null
     */
    public function setAllDailyAsProcessed($recipients, $result)
    {
        foreach ($recipients as $r) {
            $this->setDailyAsProcessed($r->id, $result);
        }
        if (php_sapi_name() == 'cli') {
            return true;
        }

        return null;
    }

    /**
     * setAllWeeklyAsProcessed method.
     *
     * @param array $recipients settings for recipients
     * @param string $result of processing
     * @return bool|null
     */
    public function setAllWeeklyAsProcessed($recipients, $result)
    {
        foreach ($recipients as $r) {
            $this->setWeeklyAsProcessed($r->id, $result);
        }
        if (php_sapi_name() == 'cli') {
            return true;
        }

        return null;
    }

    /**
     * filterEvents method.
     *
     * @param array $recipient settings for recipient
     * @param \App\Model\Entity\Event[] $events which need filtered
     * @return \App\Model\Entity\Event[] $events
     */
    public function filterEvents($recipient, $events)
    {
        if (!$recipient['all_categories']) {
            $selectedCategories = [];
            $categories = $this->CategoriesMailingList->find()
                ->where(['mailing_list_id' => $recipient['id']]);
            foreach ($categories as $category) {
                $selectedCategories[] = $category->category_id;
            }
            foreach ($events as $key => $event) {
                if (!in_array($event->category_id, $selectedCategories)) {
                    unset($events[$key]);
                }
            }
        }

        return $events;
    }

    /**
     * A duplication of the TextHelper method with serial comma added
     *
     * @param array $list needs converted
     * @param string $and the actual word "and"
     * @param string $separator a comma for the array to implode with
     * @return string
     */
    public function toList($list, $and = 'and ', $separator = ', ')
    {
        if (count($list) > 1) {
            $and = count($list) > 2 ? ', ' . $and : ' ' . $and;
            $retval = implode($separator, array_slice($list, null, -1));
            $retval .= $and;
            $retval .= array_pop($list);

            return $retval;
        }
        if (count($list) <= 1) {
            return array_pop($list);
        }

        return null;
    }

    /**
     * getSettingsDisplay method.
     *
     * @param array $recipient settings for recipient
     * @return null
     */
    public function getSettingsDisplay($recipient)
    {
        // Categories
        $eventTypes = 'All events';
        if (!$recipient['all_categories']) {
            $selectedCategories = $this->CategoriesMailingList->find()
                ->where(['mailing_list_id' => $recipient['id']]);
            $categoryNames = [];
            foreach ($selectedCategories as $sc) {
                $category = $this->Categories->get($sc->category_id);
                $categoryNames[] = $category->name;
            }
            $eventTypes = 'Only ' . $this->toList($categoryNames);
        }

        // Frequency
        $days = $this->getDays();
        $selectedDays = [];
        foreach (array_keys($days) as $day) {
            $dailyDay = "daily_$day";
            if ($recipient->$dailyDay) {
                $selectedDays[] = $days[$day];
            }
        }
        $dayCount = count($selectedDays);
        if ($dayCount == 7 || $dayCount == 0) {
            $frequency = $recipient['weekly'] ? 'Weekly' : '?';
        }
        if ($dayCount > 0 && $dayCount < 7) {
            $frequency = 'Daily on ' . $this->toList($selectedDays);
            if ($recipient['weekly']) {
                $frequency .= ' and weekly';
            }
        }

        return compact('eventTypes', 'frequency');
    }

    /**
     * Returns a welcome message if $recipientId is not set or
     * corresponds to a user who hasn't received any emails yet,
     * null otherwise.
     *
     * @param int $recipientId for who needs a welcome message
     * @return NULL|string
     */
    public function getWelcomeMessage($recipientId = null)
    {
        if ($recipientId && $this->isNewSubscriber($recipientId)) {
            $message = 'Thanks for signing up for the Muncie Events ';
            $message .= 'mailing list! Don\'t hesitate to contact us ';
            $message .= '(' . Router::url(['controller' => 'pages', 'action' => 'contact'], true) . ') ';
            $message .= 'if you have any questions, comments, or suggestions. ';
            $message .= 'Remember that at any time, you can adjust your settings and ';
            $message .= 'change how often you receive these emails and what types of ';
            $message .= "events you're interested in hearing about.";

            return $message;
        }

        return null;
    }

    /**
     * Sends the daily version of the event email.
     *
     * @param array $recipient settings
     * @param \App\Model\Entity\Event[] $events for the day
     * @return mixed
     */
    public function sendDaily($recipient, $events)
    {
        $recipientId = $recipient['id'];

        // Make sure there are events to begin with
        if (empty($events)) {
            $this->setDailyAsProcessed($recipientId, 2);

            return [false, 'Email not sent to ' . $recipient['email'] . ' because there are no events to report.'];
        }

        // Eliminate any events that this user isn't interested in
        $events = $this->filterEvents($recipient, $events);

        // Fake sending an email if testing
        if (php_sapi_name() == 'cli') {
            $eventTitles = [];
            foreach ($events as $e) {
                $eventTitles[] = $e->title;
            }
            $message = 'Email would have been sent to ' . $recipient['email'] . '<br />Events: ' . implode('; ', $eventTitles);

            return [true, $message];
        }

        // Make sure there are events left
        if (empty($events)) {
            $eventCategories = [];
            foreach ($events as $event) {
                $eventCategories[] = $event->category_id;
            }
            $this->setDailyAsProcessed($recipientId, 3);
            $message = 'No events to report, resulting from ' . $recipient['email'] . '\'s settings<br>';
            $message .= 'Selected: ' . $recipient['categories'] . '<br>';
            $message .= 'Available: ' . (empty($eventCategories) ? 'None' : implode(',', $eventCategories));

            return [false, $message];
        }

        // Send real email
        $recipientId = $recipient['id'];
        $email = new Email('mailing_list');
        $subject = 'Today in Muncie: ' . date("l, M j");
        $email
            ->setTo($recipient['email'])
            ->setSubject($subject)
            ->setTemplate('daily')
            ->setEmailFormat('both')
            ->setViewVars([
                'titleForLayout' => $subject,
                'events' => $events,
                'recipient_email' => $recipient['email'],
                'recipient_id' => $recipient['id'],
                'date' => date("l, F jS, Y"),
                'hash' => $this->getHash($recipientId),
                'welcome_message' => $this->getWelcomeMessage($recipientId),
                'settings_display' => $this->getSettingsDisplay($recipient)
            ]);
        $email->viewBuilder()->setHelpers(['Html', 'Text']);
        if ($email->send()) {
            $this->setDailyAsProcessed($recipientId, 0);

            return [true, "Email sent to " . $recipient['email']];
        }
        if (!$email->send()) {
            $this->setDailyAsProcessed($recipientId, 1);

            return [false, "Error sending email to " . $recipient['email']];
        }

        return null;
    }

    /**
     * Sends the weekly version of the event email.
     *
     * @param array $recipient settings
     * @param \App\Model\Entity\Event[] $events for the day
     * @return mixed
     */
    public function sendWeekly($recipient, $events)
    {
        $recipientId = $recipient['id'];

        $eventsCount = count($events);
        if (!$eventsCount) {
            $this->setWeeklyAsProcessed($recipientId, 2);

            return [false, 'Email not sent to ' . $recipient['email'] . ' because there are no events to report.'];
        }

        // Eliminate any events that this user isn't interested in
        $events = $this->filterEvents($recipient, $events);

        $recipientCategories = $this->CategoriesMailingList->find()
            ->where(['mailing_list_id' => $recipient['id']]);
        $cats = [];
        foreach ($recipientCategories as $recs) {
            $cats[] = $recs;
        }

        // Fake sending an email if testing
        if (php_sapi_name() == 'cli') {
            $eventTitles = [];
            foreach ($events as $daysEvents) {
                foreach ($daysEvents as $k => $e) {
                    $eventTitles[] = $e->title;
                }
            }
            $message = 'Email would have been sent to ' . $recipient['email'] . '<br />Events: ' . implode('; ', $eventTitles);

            return [true, $message];
        }

        // Make sure there are events left
        if (empty($events)) {
            // No events to report to this user today.
            $eventCategories = [];
            foreach ($events as $event) {
                $eventCategories[] = $event->category_id;
            }
            $this->setWeeklyAsProcessed($recipientId, 3);
            $message = 'No events to report, resulting from ' . $recipient['email'] . '\'s settings. ';
            $message .= 'Selected: ' . print_r($cats) . ' ';
            $message .= 'Available: ' . (empty($eventCategories) ? 'None' : implode(',', $eventCategories));

            return [false, $message];
        }

        // Send real email
        $recipientId = $recipient['id'];
        $email = new Email('mailing_list');
        $subject = 'Upcoming Week in Muncie: ' . date("M j");
        $email
            ->setTo($recipient['email'])
            ->setSubject($subject)
            ->setTemplate('weekly')
            ->setEmailFormat('both')
            ->setViewVars([
                'titleForLayout' => $subject,
                'events' => $events,
                'recipient_email' => $recipient['email'],
                'recipient_id' => $recipient['id'],
                'date' => date("l, F jS, Y"),
                'hash' => $this->getHash($recipientId),
                'welcome_message' => $this->getWelcomeMessage($recipientId),
                'settings_display' => $this->getSettingsDisplay($recipient)
            ]);
        $email->viewBuilder()->setHelpers(['Html', 'Text']);
        if ($email->send()) {
            $this->setWeeklyAsProcessed($recipientId, 0);

            return [true, 'Email sent to ' . $recipient['email']];
        }
        if (!$email->send()) {
            $this->setWeeklyAsProcessed($recipientId, 1);

            return [false, 'Error sending email to ' . $recipient['email']];
        }

        return null;
    }
}
