<?php
namespace App\Controller;

use Cake\ORM\TableRegistry;

/**
 * MailingList Controller
 */
class MailingListController extends AppController
{
    /**
     * Determines whether or not the user is authorized to make the current request
     *
     * @param User|null $user User entity
     * @return bool
     */
    public function isAuthorized($user = null)
    {
        if (isset($user)) {
            if ($user['role'] == 'admin') {
                return true;
            }
        }
        $actions = ['join', 'settings'];
        if (in_array($this->request->getParam('action'), $actions)) {
            return true;
        }

        return false;
    }
    /**
     * Initialization hook method.
     *
     * @return void
     */
    public function initialize()
    {
        parent::initialize();

        $this->Categories = TableRegistry::get('Categories');
        $this->Events = TableRegistry::get('Events');

        $this->Auth->allow(['join', 'sendDaily', 'sendWeekly', 'settings']);
    }

    /**
     * sendDailyEmail method
     *
     * @param \App\Model\Entity\Event[] $events Event entities
     * @param array $recipient mailing list recipient
     * @return $result
     */
    private function sendDailyEmail($events, $recipient)
    {
        list($result, $message) = $this->MailingList->sendDaily($recipient, $events);
        if ($result) {
            $this->Flash->success($message);
        }
        if (!$result) {
            $this->Flash->error($message);
        }

        return $result;
    }

    /**
     * sendWeeklyEmailPr method
     *
     * @param \App\Model\Entity\Event[] $events Event entities
     * @param array $recipient mailing list recipient
     * @return $result
     */
    private function sendWeeklyEmailPr($events, $recipient)
    {
        list($result, $message) = $this->MailingList->sendWeekly($recipient, $events);
        if ($result) {
            $this->Flash->success($message);
        }
        if (!$result) {
            $this->Flash->error($message);
        }

        return $result;
    }

    /**
     * sendDaily method
     *
     * @return null;
     */
    public function sendDaily()
    {
        // Make sure there are recipients
        $recipients = $this->MailingList->getDailyRecipients();
        if (empty($recipients)) {
            $this->Flash->success('No recipients found for today.');

            return null;
        }

        // Make sure there are events to report
        list($year, $mon, $day) = $this->MailingList->getTodayYMD();
        $events = $this->Events->getEventsOnDay($year, $mon, $day);
        if (empty($events)) {
            $this->MailingList->setAllDailyAsProcessed($recipients, 'd');
            $this->Flash->success('No events to inform anyone about today.');

            return null;
        }

        // Send emails
        $emailAddresses = [];
        $recipientArray = [];
        foreach ($recipients as $recipient) {
            $this->sendDailyEmail($events, $recipient);
            $emailAddresses[] = $recipient['MailingList']['email'];
            $recipientArray[] = $recipient;
        }
        $this->Flash->success(count($events) . ' total events, sent to ' . count($recipientArray) . ' recipients: ' . implode(', ', $emailAddresses));

        return null;
    }

    /**
     * sendWeekly method
     *
     * @return null;
     */
    public function sendWeekly()
    {
        // Make sure that today is the correct day
        if (!$this->MailingList->getWeeklyDeliveryDay()) {
            $this->Flash->success('Today is not the day of the week designated for delivering weekly emails.');

            return null;
        }

        // Make sure there are recipients
        $recipients = $this->MailingList->getWeeklyRecipients();
        if (empty($recipients)) {
            $this->Flash->success('No recipients found for this week.');

            return null;
        }

        // Make sure there are events to report
        list($year, $mon, $day) = $this->MailingList->getTodayYMD();
        $events = $this->Events->getEventsUpcomingWeek($year, $mon, $day, true);
        if (empty($events)) {
            $this->MailingList->setAllWeeklyAsProcessed($recipients, 1);
            $this->Flash->success('No events to informa anyone about this week.');

            return null;
        }

        // Send emails
        $successCount = 0;
        foreach ($recipients as $recipient) {
            if ($this->sendWeeklyEmailPr($events, $recipient)) {
                $successCount++;
            }
        }
        $eventsCount = 0;
        foreach ($events as $day => $dEvents) {
            $eventsCount += count($dEvents);
        }
        $this->Flash->success("$eventsCount total events sent to $successCount recipients!");

        return null;
    }

    /**
     * readFormDataPr method
     *
     * @param \App\Model\Entity\MailingList|\Cake\Datasource\EntityInterface|null $mailingList mailingList entity
     * @return \App\Model\Entity\MailingList $mailingList mailingList entity
     */
    private function readFormDataPr($mailingList)
    {
        $mailingList->email = strtolower(trim($mailingList->email));

        // Is this person new?
        $mailingList->new_subscriber = $this->MailingList->isNewSubscriberEmail($mailingList->email);

        // If joining for with default settings
        if (isset($mailingList['settings'])) {
            if ($mailingList['settings'] == 'default') {
                $mailingList->weekly = 1;
                $mailingList->all_categories = 1;

                return $mailingList;
            }
        }

        // All event types
        // If the user did not select 'all events', but has each category individually selected, set 'all_categories' to true
        $allCatSelected = ($mailingList['event_categories'] == 'all');
        if ($allCatSelected) {
            $mailingList->all_categories = 1;
        }

        if (!$allCatSelected) {
            $selectedCatCount = count($mailingList['selected_categories']);
            $allCatCount = $this->Categories->find()->count();
            if ($selectedCatCount == $allCatCount) {
                $mailingList->all_categories = 1;
            }
            if ($selectedCatCount != $allCatCount) {
                $mailingList->all_categories = 0;
            }
        }

        if ($mailingList['frequency'] == 'weekly') {
            $mailingList->weekly = 1;
        }

        $days = $this->MailingList->getDays();
        if ($mailingList['frequency'] == 'daily') {
            foreach ($days as $code => $day) {
                $dailyCode = 'daily_' . $code;
                $mailingList->$dailyCode = 1;
            }
        }
        if ($mailingList['frequency'] == 'custom') {
            foreach ($days as $code => $day) {
                $dailyCode = 'daily_' . $code;
                $value = $mailingList->$dailyCode;
                $mailingList->$dailyCode = $value;
            }
        }

        $mailingList->new_subscriber = 1;

        return $mailingList;
    }

    /**
     * addCategoryJoins method
     *
     * @param \App\Model\Entity\MailingList $mailingList mailingList entity
     * @return \App\Model\Entity\MailingList $mailingList mailingList entity
     */
    private function addCategoryJoins($mailingList)
    {
        $this->loadModel('Categories');
        $this->loadModel('CategoriesMailingList');
        $allCategories = $this->Categories->find('list')->toArray();

        // If joining for the first time with default settings
        if (isset($mailingList['settings'])) {
            if ($mailingList['settings'] == 'default') {
                foreach ($allCategories as $key => $cat) {
                    $newJoin = $this->CategoriesMailingList->newEntity();
                    $newJoin->mailing_list_id = $mailingList->id;
                    $newJoin->category_id = $key;
                    $this->CategoriesMailingList->save($newJoin);
                }

                return $mailingList;
            }
        }

        // All event types
        // If the user did not select 'all events', but has each category individually selected, set 'all_categories' to true
        if (!$mailingList->all_categories) {
            $selectedCatCount = count($mailingList['selected_categories']);
            $allCatCount = count($allCategories);
            if ($selectedCatCount == $allCatCount) {
                foreach ($allCategories as $catId => $catName) {
                    $newJoin = $this->CategoriesMailingList->newEntity();
                    $newJoin->mailing_list_id = $mailingList->id;
                    $newJoin->category_id = $catId;
                    $this->CategoriesMailingList->save($newJoin);
                }
            }
            if ($selectedCatCount != $allCatCount) {
                // unset the categories so that there's no duplicates
                $cats = $this->CategoriesMailingList->find()
                    ->where(['mailing_list_id' => $mailingList->id])
                    ->toArray();
                foreach ($cats as $cat) {
                    $del = $this->CategoriesMailingList->get($cat->id);
                    $this->CategoriesMailingList->delete($del);
                }
                foreach ($mailingList['selected_categories'] as $key => $scat) {
                    if ($scat) {
                        $newJoin = $this->CategoriesMailingList->newEntity();
                        $newJoin->mailing_list_id = $mailingList->id;
                        $newJoin->category_id = $key;
                        $this->CategoriesMailingList->save($newJoin);
                    }
                }
            }
        }

        // Finally, let's just say someone wants all events
        if ($mailingList->all_categories) {
            foreach ($allCategories as $cat) {
                $fullCat = $this->Categories->find()
                    ->where(['name' => $cat])
                    ->first();
                $newJoin = $this->CategoriesMailingList->newEntity();
                $newJoin->mailing_list_id = $mailingList->id;
                $newJoin->category_id = $fullCat->id;
                $this->CategoriesMailingList->save($newJoin);
            }
        }

        return null;
    }

    /**
     * Add method
     * as turned into a "join" method, heh
     *
     * @return void
     */
    public function join()
    {
        $titleForLayout = 'Join Muncie Events Mailing List';
        $this->set('titleForLayout', $titleForLayout);
        $mailingList = $this->MailingList->newEntity();
        if ($this->request->is('post')) {
            $mailingList = $this->MailingList->patchEntity($mailingList, $this->request->getData());
            $mailingList = $this->readFormDataPr($mailingList);
            if ($this->MailingList->save($mailingList)) {
                $this->addCategoryJoins($mailingList);
                $this->Flash->success(__('The mailing list has been saved.'));
            }
            if (!$this->MailingList->save($mailingList)) {
                $this->Flash->error(__('The mailing list could not be saved. Please, try again.'));
            }
        }
        $categories = $this->MailingList->Categories->find('list', ['limit' => 200]);
        $this->set(compact('mailingList', 'categories'));
        $this->set('_serialize', ['mailingList']);

        $days = $this->MailingList->getDays();
        $this->set('days', $days);
    }

    /**
     * reset mailing list members processed time
     *
     * @return void
     */
    public function resetProcessedTime()
    {
        $recipients = $this->MailingList->find('list');
        $x = 0;
        foreach ($recipients as $id => $email) {
            $recipient = $this->MailingList->get($id);
            $recipient->processed_daily = null;
            $recipient->processed_weekly = null;
            $this->MailingList->save($recipient);
            $x = $x + 1;
        }
        $this->Flash->success($x . ' mailing list members\' "last processed" times reset.');
    }

    /**
     * bulk add users to the mailing list
     *
     * @return void
     */
    public function bulkAdd()
    {
        $this->set([
            'titleForLayout' => 'Bulk Add - Mailing List'
        ]);

        if (!empty($this->request->getData())) {
            $addresses = explode("\n", $this->request->getData('email_addresses'));
            $retainedAddresses = [];
            foreach ($addresses as $address) {
                $address = trim(strtolower($address));
                if (!$address) {
                    continue;
                }

                // Set
                $mailingList = $this->MailingList->newEntity();
                $mailingList->email = $address;
                $mailingList->weekly = 1;
                $mailingList->all_categories = 1;

                if ($this->MailingList->save($mailingList)) {
                    $this->Flash->success("$address added.");
                }
                if (!$this->MailingList->save($mailingList)) {
                    $retainedAddresses[] = $address;
                    $this->Flash->error("Error adding $address.");
                }
            }
        }
    }

    /**
     * delete & unassociate users from the mailing list
     *
     * @param int|null $recipientId for unsubscribing
     * @return null
     */
    private function unsubscribePr($recipientId)
    {
        $recipient = $this->MailingList->get($recipientId);
        if ($this->MailingList->delete($recipient)) {
            // get rid of the cat joins as well
            $joins = $this->CategoriesMailingList->find()
                ->where(['mailing_list_id' => $recipientId])
                ->toArray();
            foreach ($joins as $join) {
                $del = $this->CategoriesMailingList->get($join['id']);
                $this->CategoriesMailingList->delete($del);
            }
            $this->Flash->success('You have been removed from the mailing list.');

            return null;
        }
        $this->Flash->error('There was an error removing you from the mailing list. Please contact support for assistance.');

        return null;
    }

    /**
     * Run special validation in addition to MailingList->validates(), returns TRUE if data is valid
     *
     * @param int|null $recipientId for validating the form
     * @return bool
     */
    private function validateMailingListForm($recipientId = null)
    {
        $noErrors = true;

        // If updating an existing subscription
        if ($recipientId) {
            $emailInUse = $this->MailingList->find()
                ->where(['email' => $this->request->getData('email')])
                ->andWhere(['id IS NOT' => $recipientId])
                ->count();
            if ($emailInUse) {
                $noErrors = false;
                $this->Flash->error('Cannot change to that email address because another subscriber is currently signed up with it.');
            }
        }

        // If creating a new subscription
        if (!$recipientId) {
            $emailInUse = $this->MailingList->find()
                ->where(['email' => $this->request->getData('email')])
                ->count();
            if ($emailInUse) {
                $noErrors = false;
                $this->Flash->error('That address is already subscribed to the mailing list.');
            }
        }
        $allCategories = ($this->request->getData('event_categories') == 'all');
        $noCategories = empty($this->request->getData('event_categories'));
        if (!$allCategories && $noCategories) {
            $noErrors = false;
            $this->set('categories_error', 'At least one category must be selected.');
        }
        $frequency = $this->request->getData('frequency');
        $weekly = $this->request->getData('weekly');
        if ($frequency == 'custom' && ! $weekly) {
            $selectedDaysCount = 0;
            $days = $this->MailingList->getDays();
            foreach ($days as $code => $day) {
                $selectedDaysCount += $this->request->getData("daily_$code");
            }
            if (! $selectedDaysCount) {
                $noErrors = false;
                $this->set('frequency_error', 'You\'ll need to pick either the weekly email or at least one daily email to receive.');
            }
        }

        return $noErrors;
    }

    /**
     * settings method.
     *
     * @param int|null $recipientId of the mailing list recipient
     * @param string|null $hash to update mailing list settings
     * @return null
     */
    public function settings($recipientId = null, $hash = null)
    {
        $this->set([
            'titleForLayout' => 'Update Mailing List Settings',
            'days' => $this->MailingList->getDays(),
            'categories' => $this->Categories->find('list')->toArray()
        ]);

        if ($this->request->is('ajax')) {
            $this->viewBuilder()->setLayout('ajax');
        }

        // Make sure link is valid
        if (!$recipientId || $hash != $this->MailingList->getHash($recipientId)) {
            $this->Flash->error('It appears that you clicked on a broken link. If you copied and
                    pasted a URL to get here, you may not have copied the whole address.
                    Please contact support if you need assistance.');

            return null;
        }

        // Make sure subscriber exists
        $recipient = $this->MailingList->get($recipientId);
        if (!$recipient) {
            $this->Flash->error('It looks like you\'re trying to change settings for a user who is no longer
                    on our mailing list. Please contact support if you need assistance.');

            return null;
        }

        if ($this->request->is('post')) {
            //unset the recipient
            $recipient->all_categories = null;
            $recipient->weekly = null;
            $days = $this->MailingList->getDays();
            foreach ($days as $code => $day) {
                $dailyCode = 'daily_' . $code;
                $recipient->$dailyCode = null;
            }
            $recipient->modified = date('Y-m-d H:i:s');
            $this->MailingList->save($recipient);

            // Unsubscribe
            if ($this->request->getData('unsubscribe') == 1) {
                return $this->unsubscribePr($recipientId);
            }

            $recipient = $this->MailingList->patchEntity($recipient, $this->request->getData());
            $this->readFormDataPr($recipient);
            $this->addCategoryJoins($recipient);

            // If there's an associated user, update its email too
            $userId = $this->Users->getIdFromEmail($recipient->email);
            if ($userId) {
                $user = $this->Users->get($userId);
                $user->email = $recipient->email;
                $this->Users->save($user);
            }

            // Update settings
            if ($this->validateMailingListForm($recipientId)) {
                if ($this->MailingList->save($recipient)) {
                    $this->Flash->success('Your mailing list settings have been updated.');

                    return null;
                }
                $this->Flash->error('Please try again, or contact support for assistance.');

                return null;
            }
        }
        $this->set(compact('recipient', 'recipientId', 'hash'));

        return null;
    }
}
