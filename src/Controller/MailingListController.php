<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;

/**
 * MailingList Controller
 *
 * @property \App\Model\Table\MailingListTable $MailingList
 */
class MailingListController extends AppController
{
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

        $this->Auth->allow(['join']);
    }

    /**
     * sendDailyEmailPr method
     *
     * @param ResultSet $events Event entities
     * @param string $recipient mailing list recipient
     * @param bool|false $testing whether or not this is testing mode
     * @return $result
     */
    private function sendDailyEmailPr($events, $recipient, $testing = false)
    {
        list($result, $message) = $this->MailingList->sendDaily($recipient, $events, $testing);
        if ($result) {
            $this->Flash->success($message);
        } else {
            $this->Flash->error($message);
        }

        return $result;
    }

    /**
     * sendWeeklyEmailPr method
     *
     * @param ResultSet $events Event entities
     * @param string $recipient mailing list recipient
     * @param bool|false $testing whether or not this is testing mode
     * @return $result
     */
    private function sendWeeklyEmailPr($events, $recipient, $testing = false)
    {
        list($result, $message) = $this->MailingList->sendWeekly($recipient, $events, $testing);
        if ($result) {
            $this->Flash->success($message);
        } else {
            $this->Flash->error($message);
        }

        return $result;
    }

    /**
     * sendWeekly method
     *
     * @return Cake\View\Helper\FlashHelper
     */
    public function sendDaily()
    {
        // Make sure there are recipients
        $recipients = $this->MailingList->getDailyRecipients();
        if (empty($recipients)) {
            return $this->Flash->success('No recipients found for today.');
        }

        // Make sure there are events to report
        list($year, $mon, $day) = $this->MailingList->getTodayYMD();
        $events = $this->Events->getEventsOnDay($year, $mon, $day);
        if (empty($events)) {
            $this->MailingList->setAllDailyAsProcessed($recipients, 'd');

            return $this->Flash->success('No events to inform anyone about today.');
        }

        // Send emails
        $emailAddresses = [];
        foreach ($recipients as $recipient) {
            $this->sendDailyEmailPr($events, $recipient);
            $emailAddresses[] = $recipient['MailingList']['email'];
        }

        return $this->Flash->success(count($events) . ' total events, sent to ' . count($recipients) . ' recipients: ' . implode(', ', $emailAddresses));
    }

    /**
     * sendWeekly method
     *
     * @return Cake\View\Helper\FlashHelper
     */
    public function sendWeekly()
    {
        // Make sure that today is the correct day
        if (! $this->MailingList->testing_mode && ! $this->MailingList->getWeeklyDeliveryDay()) {
            return $this->Flash->success('Today is not the day of the week designated for delivering weekly emails.');
        }

        // Make sure there are recipients
        $recipients = $this->MailingList->getWeeklyRecipients();
        if (empty($recipients)) {
            return $this->Flash->success('No recipients found for this week.');
        }

        // Make sure there are events to report
        list($year, $mon, $day) = $this->MailingList->getTodayYMD();
        $events = $this->Event->getEventsUpcomingWeek($year, $mon, $day, true);
        if (empty($events)) {
            $this->MailingList->setAllWeeklyAsProcessed($recipients);

            return $this->Flash->success('No events to informa anyone about this week.');
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

        return $this->Flash->success("$eventsCount total events sent to $successCount recipients!");
    }

    /**
     * readFormDataPr method
     *
     * @param ResultSet $mailingList mailingList entity
     * @return ResultSet $mailingList
     */
    private function readFormDataPr($mailingList)
    {
        $this->loadModel('Categories');
        $this->loadModel('CategoriesMailingList');
        $allCategories = $this->MailingList->Categories->getAll();
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
        if (!$allCatSelected) {
            $selectedCatCount = count($mailingList->selected_categories);
            $allCatCount = count($allCategories);
            if ($selectedCatCount == $allCatCount) {
                $allCatSelected = true;
                $mailingList->all_categories = 1;
            }
            if ($selectedCatCount != $allCatCount) {
                $mailingList->all_categories = 0;
            }
        }

        // Daily frequency
        $days = $this->MailingList->getDays();
        foreach ($days as $code => $day) {
            $dailyCode = 'daily_' . $code;
            $value = $mailingList->$dailyCode;
            $mailingList->$dailyCode = $value;
        }

        $mailingList->new_subscriber = 1;
        $mailingList->all_categories = 1;

        return $mailingList;
    }

    /**
     * addCategoryJoins method
     *
     * @param ResultSet $mailingList mailingList entity
     * @return ResultSet $mailingList
     */
    private function addCategoryJoins($mailingList)
    {
        $this->loadModel('Categories');
        $this->loadModel('CategoriesMailingList');
        $allCategories = $this->MailingList->Categories->getAll();

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
            $selectedCatCount = count($mailingList->selected_categories);
            $allCatCount = count($allCategories);
            if ($selectedCatCount == $allCatCount) {
                foreach ($allCategories as $cat) {
                    $newJoin = $this->CategoriesMailingList->newEntity();
                    $newJoin->mailing_list_id = $mailingList->id;
                    $newJoin->category_id = $cat->id;
                    $this->CategoriesMailingList->save($newJoin);
                }
            }
            if ($selectedCatCount != $allCatCount) {
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
                $newJoin = $this->CategoriesMailingList->newEntity();
                $newJoin->mailing_list_id = $mailingList->id;
                $newJoin->category_id = $cat->id;
                $this->CategoriesMailingList->save($newJoin);
            }
        }
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
            } else {
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
        foreach ($recipients as $id => $email) {
            $recipient = $this->MailingList->get($id);
            $recipient->processed_daily = null;
            $recipient->processed_weekly = null;
            $this->MailingList->save($recipient);
        }
        $this->Flash->success(count($recipients) . ' mailing list members\' "last processed" times reset.');
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

        if (!empty($this->request->data)) {
            $addresses = explode("\n", $this->request->data['email_addresses']);
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
                } else {
                    $retainedAddresses[] = $address;
                    $this->Flash->error("Error adding $address.");
                }
            }
            $this->request->data['email_addresses'] = implode("\n", $retainedAddresses);
        }
    }

    /**
     * set default mailing list values
     *
     * @param str|null $recipient for values
     * @return void
     */
    private function setDefaultValuesPr($recipient = null)
    {
        $this->request->data = $this->MailingList->getDefaultFormValues($recipient);
    }

    /**
     * delete & unassociate users from the mailing list
     *
     * @param int|null $recipientId for unsubscribing
     * @return Cake\View\Helper\FlashHelper
     */
    private function unsubscribePr($recipientId)
    {
        if ($this->MailingList->delete($recipientId)) {
            // Un-associate associated User
            // $userId = $this->User->field('id', array('mailing_list_id' => $recipientId));
            // if ($userId) {
            //    $this->User->id = $userId;
            //    $this->User->saveField('mailing_list_id', null);
            // }
            return $this->Flash->success('You have been removed from the mailing list.');
        }

        return $this->Flash->error('There was an error removing you from the mailing list. Please <a href="/contact">contact support</a> for assistance.');
    }

    /**
     * Run special validation in addition to MailingList->validates(), returns TRUE if data is valid
     *
     * @param int|null $recipientId for validating the form
     * @return bool
     */
    private function validateFormPr($recipientId = null)
    {
        $errorFound = false;

        // If updating an existing subscription
        if ($recipientId) {
            $emailInUse = $this->MailingList->find()
                ->where(['email' => $this->request->data['email']])
                ->andWhere(['id NOT' => $recipientId])
                ->count();
            if ($emailInUse) {
                $errorFound = true;
                $this->MailingList->validationErrors['email'] = 'Cannot change to that email address because another subscriber is currently signed up with it.';
            }

        // If creating a new subscription
        } else {
            $emailInUse = $this->MailingList->find()
                ->where(['email' => $this->request->data['email']])
                ->count();
            if ($emailInUse) {
                $errorFound = true;
                $this->MailingList->validationErrors['email'] = 'That address is already subscribed to the mailing list.';
            }
        }
        $allCategoriesSelected = ($this->request->data['event_categories'] == 'all');
        $noCategoriesSelected = empty($this->request->data['selected_categories']);
        if (! $allCategoriesSelected && $noCategoriesSelected) {
            $errorFound = true;
            $this->set('categories_error', 'At least one category must be selected.');
        }
        $frequency = $this->request->data['frequency'];
        $weekly = $this->request->data['weekly'];
        if ($frequency == 'custom' && ! $weekly) {
            $selectedDaysCount = 0;
            $days = $this->MailingList->getDays();
            foreach ($days as $code => $day) {
                $selectedDaysCount += $this->request->data["daily_$code"];
            }
            if (! $selectedDaysCount) {
                $errorFound = true;
                $this->set('frequency_error', 'You\'ll need to pick either the weekly email or at least one daily email to receive.');
            }
        }

        return ($this->MailingList->validates() && !$errorFound);
    }

    /**
     * settings method.
     *
     * @param int|null $recipientId of the mailing list recipient
     * @param string|null $hash to update mailing list settings
     * @return Cake\View\Helper\FlashHelper
     */
    public function settings($recipientId = null, $hash = null)
    {
        $this->set([
            'titleForLayout' => 'Update Mailing List Settings',
            'days' => $this->MailingList->getDays(),
            'categories' => $this->Categories->getAll()
        ]);

        if ($this->request->is('ajax')) {
            $this->layout = 'ajax';
        }

        // Make sure link is valid
        if (!$recipientId || $hash != $this->MailingList->getHash($recipientId)) {
            return $this->Flash->error('It appears that you clicked on a broken link. If you copied and
                    pasted a URL to get here, you may not have copied the whole address.
                    Please <a href="/contact">contact support</a> if you need assistance.');
        }

        // Make sure subscriber exists
        $recipient = $this->MailingList->get($recipientId);
        if (!$recipient) {
            return $this->Flash->error('It looks like you\'re trying to change settings for a user who is no longer
                    on our mailing list. Please <a href="/contact">contact support</a> if you need assistance.');
        }

        if ($this->request->is('post')) {
            // Unsubscribe
            if ($this->request->data['unsubscribe']) {
                return $this->unsubscribePr($recipientId);
            }

            $this->readFormDataPr();

            // If there's an associated user, update its email too
            // $userId = $this->MailingList->getAssociatedUserId();
            // if ($userId) {
            //    $this->User->id = $userId;
            //    $this->User->saveField('email', $this->request->data['MailingList']['email']);
            // }

            // Update settings
            if ($this->validateFormPr($recipientId)) {
                if ($this->MailingList->save()) {
                    return $this->Flash->success('Your mailing list settings have been updated.');
                }

                return $this->Flash->error('Please try again, or <a href="/contact">contact support</a> for assistance.');
            }
        } else {
            $this->setDefaultValuesPr($recipient);
        }

        $this->set(compact('recipient', 'recipientId', 'hash'));
    }
}
