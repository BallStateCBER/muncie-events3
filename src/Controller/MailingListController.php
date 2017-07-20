<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Routing\Router;
use Cake\ORM\TableRegistry;

/**
 * MailingList Controller
 *
 * @property \App\Model\Table\MailingListTable $MailingList
 */
class MailingListController extends AppController
{
    public function initialize()
    {
        $this->Events = TableRegistry::get('Events');
    }
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

    public function sendDaily()
    {
        // Make sure there are recipients
        $recipients = $this->MailingList->getDailyRecipients();
        if (empty($recipients)) {
            return $this->renderMessage([
                'title' => 'Daily Emails Not Sent',
                'message' => 'No recipients found for today',
                'class' => 'notification'
            ]);
        }

        // Make sure there are events to report
        list($year, $mon, $day) = $this->MailingList->getTodayYMD();
        $events = $this->Events->getEventsOnDay($year, $mon, $day);
        if (empty($events)) {
            $this->MailingList->setAllDailyAsProcessed($recipients, 'd');
            return $this->renderMessage([
                'title' => 'Daily Emails Not Sent',
                'message' => 'No events to inform anyone about today',
                'class' => 'notification'
            ]);
        }

        // Send emails
        $emailAddresses = [];
        foreach ($recipients as $recipient) {
            $this->sendDailyEmailPr($events, $recipient);
            $emailAddresses[] = $recipient['MailingList']['email'];
        }
        return $this->renderMessage([
            'title' => 'Daily Emails Sent',
            'message' => count($events).' total events, sent to '.count($recipients).' recipients: '.implode(', ', $emailAddresses),
            'class' => 'success'
        ]);
    }

    public function sendWeekly()
    {
        // Make sure that today is the correct day
        if (! $this->MailingList->testing_mode && ! $this->MailingList->getWeeklyDeliveryDay()) {
            return $this->renderMessage([
                'title' => 'Weekly Emails Not Sent',
                'message' => 'Today is not the day of the week designated for delivering weekly emails.',
                'class' => 'notification'
            ]);
        }

        // Make sure there are recipients
        $recipients = $this->MailingList->getWeeklyRecipients();
        if (empty($recipients)) {
            return $this->renderMessage([
                'title' => 'Weekly Emails Not Sent',
                'message' => 'No recipients found for this week',
                'class' => 'notification'
            ]);
        }

        // Make sure there are events to report
        list($year, $mon, $day) = $this->MailingList->getTodayYMD();
        $events = $this->Event->getEventsUpcomingWeek($year, $mon, $day, true);
        if (empty($events)) {
            $this->MailingList->setAllWeeklyAsProcessed($recipients);
            return $this->renderMessage([
                'title' => 'Weekly Emails Not Sent',
                'message' => 'No events to inform anyone about this week',
                'class' => 'notification'
            ]);
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
        return $this->renderMessage([
            'title' => 'Weekly Emails Sent',
            'message' => $eventsCount.' total events, sent to '.$successCount.' recipients.',
            'class' => 'success'
        ]);
    }

    private function readFormDataPr($mailingList)
    {
        $this->loadModel('Categories');
        $this->loadModel('CategoriesMailingList');
        $allCategories = $this->MailingList->Categories->getAll();
        $mailingList->email = strtolower(trim($mailingList->email));

        // If joining for the first time with default settings
        if (isset($mailingList['settings'])) {
            if ($mailingList['settings'] == 'default') {
                $mailingList->weekly = 1;
                $mailingList->all_categories = 1;
                $mailingList->Categories = $allCategories;
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
                $mailingList->Categories = $allCategories;
            }
        }

        // Custom event types
        if (isset($mailingList->selected_categories)) {
            $mailingList->Categories = array_keys($mailingList->selected_categories);
            $mailingList->all_categories = 0;
        }

        // Weekly frequency
        $weekly = $mailingList->weekly || $mailingList['frequency'] == 'weekly';
        $mailingList->weekly = $weekly;

        // Daily frequency
        $days = $this->MailingList->getDays();
        $daily = $mailingList['frequency'] == 'daily';
        foreach ($days as $code => $day) {
            $dailyCode = 'daily_'.$code;
            $value = $daily || $mailingList->$dailyCode;
            $mailingList->$dailyCode = $value;
        }

        // custom day frequency
        if ($mailingList['frequency'] == 'custom') {
            foreach ($days as $code => $day) {
                $dailyCode = 'daily_'.$code;
                $value = $mailingList->$dailyCode;
                $mailingList->$dailyCode = $value;
            }
        }

        $mailingList->new_subscriber = 1;

        return $mailingList;
    }

    /**
     * Add method
     * as turned into a "join" method, heh
     *
     * @return \Cake\Network\Response|null Redirects on successful add, renders view otherwise.
     */
    public function join()
    {
        $titleForLayout = 'Join our Mailing List';
        $this->set('titleForLayout', $titleForLayout);
        $mailingList = $this->MailingList->newEntity();
        if ($this->request->is('post')) {
            $mailingList = $this->MailingList->patchEntity($mailingList, $this->request->getData());
            $mailingList = $this->readFormDataPr($mailingList);
            if ($this->MailingList->save($mailingList)) {
                $this->Flash->success(__('The mailing list has been saved.'));

                // create linked rows between subscribers & their categories
                foreach ($mailingList->Categories as $category) {
                    $newCategory = $this->CategoriesMailingList->newEntity();
                    $newCategory->mailing_list_id = $mailingList->id;
                    if (isset($category->id)) {
                        $newCategory->category_id = $category->id;
                    } elseif (is_int($category)) {
                        $newCategory->category_id = $category;
                    }
                    $this->CategoriesMailingList->save($newCategory);
                }
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
}
