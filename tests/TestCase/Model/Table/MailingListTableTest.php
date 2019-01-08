<?php
namespace App\Test\TestCase\Model\Table;

use Cake\TestSuite\TestCase;

class MailingListTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\MailingListTable
     */
    public $MailingList;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();
    }

    /**
     * Test getTodayYMD method
     *
     * @return void
     */
    public function testGetTodayYMD()
    {
        $date = $this->MailingList->getTodayYMD();
        $today = [date('Y'), date('m'), date('d')];
        $this->assertEquals($date, $today);
    }

    /**
     * Test getDailyRecipients method
     *
     * @return void
     */
    public function testGetDailyRecipients()
    {
        $dailies = $this->MailingList->getDailyRecipients();
        $day = 'daily_' . strtolower(date('D'));
        foreach ($dailies as $daily) {
            $this->assertEquals($daily->$day, 1);
        }
        if (empty($dailies)) {
            $this->assertEquals([], $dailies);
        }
    }

    /**
     * Test getWeeklyDeliveryDay method
     *
     * @return void
     */
    public function testGetWeeklyDeliveryDay()
    {
        $weekday = $this->MailingList->getWeeklyDeliveryDay();
        if (date('l') == 'Thursday') {
            $this->assertEquals(true, $weekday);
        }
        if (date('l') != 'Thursday') {
            $this->assertEquals(false, $weekday);
        }
    }

    /**
     * Test getWeeklyRecipients method
     *
     * @return void
     */
    public function testGetWeeklyRecipients()
    {
        $weeklies = $this->MailingList->getWeeklyRecipients();
        foreach ($weeklies as $weekly) {
            $this->assertEquals($weekly->weekly, 1);
        }
        if (empty($weeklies)) {
            $this->assertEquals([], $weeklies);
        }
    }

    /**
     * Test getDays method
     *
     * @return void
     */
    public function testGetDays()
    {
        $days = $this->MailingList->getDays();
        foreach ($days as $day) {
            if ($day == date('l')) {
                $this->assertEquals($day, date('l'));
            }
        }
    }

    /**
     * Test isNewSubscriber method
     *
     * @return void
     */
    public function testIsNewSubscriber()
    {
        $mailingListWeekly = $this->MailingList->newEntity();
        $mailingListWeekly->email = 'placeholder@gmail.com';
        $mailingListWeekly->all_categories = 1;
        $mailingListWeekly->weekly = 1;
        $mailingListWeekly->new_subscriber = 1;
        $this->MailingList->save($mailingListWeekly);
        $subscriber = $this->MailingList->isNewSubscriber($mailingListWeekly->id);
        $this->assertEquals($subscriber, true);
    }

    /**
     * Test getHash method
     *
     * @return void
     */
    public function testGetHash()
    {
        $mailingListWeekly = $this->MailingList->find()
            ->where(['email' => 'edfox@bsu.edu'])
            ->first();
        $hash = $this->MailingList->getHash($mailingListWeekly->id);
        $secondHash = md5('recipient' . $mailingListWeekly->id);
        $this->assertEquals($hash, $secondHash);
    }

    /**
     * Test setDailyAsProcessed method
     *
     * @return void
     */
    public function testSetDailyAsProcessed()
    {
        $mailingListDaily = $this->MailingList->newEntity();
        $mailingListDaily->email = 'dailyplaceholder@gmail.com';
        $mailingListDaily->all_categories = 1;
        $mailingListDaily->daily_sun = 1;
        $mailingListDaily->daily_mon = 1;
        $mailingListDaily->daily_tue = 1;
        $mailingListDaily->daily_wed = 1;
        $mailingListDaily->daily_thu = 1;
        $mailingListDaily->daily_fri = 1;
        $mailingListDaily->daily_sat = 1;
        $mailingListDaily->new_subscriber = 1;
        $this->MailingList->save($mailingListDaily);
        $processed = $this->MailingList->setDailyAsProcessed($mailingListDaily->id, 0);
        $this->assertEquals($processed['new_subscriber'], 0);
    }

    /**
     * Test setWeeklyAsProcessed method
     *
     * @return void
     */
    public function testSetWeeklyAsProcessed()
    {
        $mailingListWeekly = $this->MailingList->find()
            ->where(['email' => 'edfox@bsu.edu'])
            ->first();
        $processed = $this->MailingList->setWeeklyAsProcessed($mailingListWeekly->id, 0);
        $this->assertEquals($processed['new_subscriber'], 0);
    }

    /**
     * Test setAllDailyAsProcessed method
     *
     * @return void
     */
    public function testSetAllDailyAsProcessed()
    {
        $processed = $this->MailingList->setAllDailyAsProcessed($this->MailingList->getDailyRecipients(), 0);
        $this->assertEquals(true, $processed);
    }

    /**
     * Test setAllWeeklyAsProcessed method
     *
     * @return void
     */
    public function testSetAllWeeklyAsProcessed()
    {
        $processed = $this->MailingList->setAllWeeklyAsProcessed($this->MailingList->getWeeklyRecipients(), 0);
        $this->assertEquals(true, $processed);
    }

    /**
     * Test filterEvents method
     *
     * @return void
     */
    public function testFilterEvents()
    {
        $mailingListWeekly = $this->MailingList->find()
            ->where(['email' => 'edfox@bsu.edu'])
            ->first();
        $mailingListWeekly['all_categories'] = 0;
        $this->MailingList->save($mailingListWeekly);
        $link = $this->CategoriesMailingList->newEntity();
        $link->mailing_list_id = $mailingListWeekly->id;
        $link->category_id = 1;
        $this->CategoriesMailingList->save($link);
        $events = $this->Events->find()
            ->contain(['Categories', 'EventSeries', 'Images', 'Tags', 'Users'])
            ->where(['date >=' => date('Y-m-d')])
            ->andWhere(['date <=' => date('Y-m-d', strtotime('+1 week'))])
            ->toArray();
        $events = $this->MailingList->filterEvents($mailingListWeekly, $events);
        foreach ($events as $event) {
            $this->assertEquals(1, $event->category_id);
        }
    }

    /**
     * Test toList method
     *
     * @return void
     */
    public function testToList()
    {
        $categoryList = [
            'General Events',
            'Music',
            'Art',
            'Religion'
        ];
        $list = $this->MailingList->toList($categoryList);
        if (!is_array($list)) {
            $this->assertEquals('General Events, Music, Art, and Religion', $list);

            return;
        }
    }

    /**
     * Test getSettingsDisplay method
     *
     * @return void
     */
    public function testGetSettingsDisplay()
    {
        $mailingListWeekly = $this->MailingList->find()
            ->where(['email' => 'edfox@bsu.edu'])
            ->first();
        $display = $this->MailingList->getSettingsDisplay($mailingListWeekly);
        $expected = [
            'eventTypes' => 'Only General Events',
            'frequency' => 'Weekly'
        ];
        $this->assertEquals($display, $expected);
        $join = $this->CategoriesMailingList->find()
            ->where(['mailing_list_id' => $mailingListWeekly->id])
            ->first();
        $this->CategoriesMailingList->delete($join);
    }

    /**
     * Test getWelcomeMessage method
     *
     * @return void
     */
    public function testGetWelcomeMessage()
    {
        $mailingListWeekly = $this->MailingList->find()
            ->where(['email' => 'edfox@bsu.edu'])
            ->first();
        $newRecipient = $this->MailingList->getWelcomeMessage($mailingListWeekly->id);
        $this->assertContains('Thanks for signing up for the Muncie Events', $newRecipient);
        $mailingListWeekly['new_subscriber'] = 0;
        $this->MailingList->save($mailingListWeekly);
        $oldRecipient = $this->MailingList->getWelcomeMessage($mailingListWeekly->id);
        $this->assertEquals(null, $oldRecipient);
    }

    /**
     * Test sendDaily method
     *
     * @return void
     */
    public function testSendDaily()
    {
        $mailingListDaily = $this->MailingList->find()
            ->where(['email' => 'edfox@bsu.edu'])
            ->first();
        $events = $this->Events->find()
            ->contain(['Categories', 'EventSeries', 'Images', 'Tags', 'Users'])
            ->where(['date =' => date('Y-m-d')])
            ->toArray();
        $sendDaily = $this->MailingList->sendDaily($mailingListDaily, $events);
        $this->assertEquals($sendDaily[0], !empty($events));

        $this->MailingList->delete($mailingListDaily);
    }

    /**
     * Test sendWeekly method
     *
     * @return void
     */
    public function testSendWeekly()
    {
        $mailingListWeekly = $this->MailingList->find()
            ->where(['email' => 'edfox@bsu.edu'])
            ->first();
        $events = $this->Events->find()
            ->contain(['Categories', 'EventSeries', 'Images', 'Tags', 'Users'])
            ->where(['date >=' => date('Y-m-d')])
            ->andWhere(['date <=' => date('Y-m-d', strtotime('+1 week'))])
            ->toArray();
        $sendWeekly = $this->MailingList->sendWeekly($mailingListWeekly, $events);
        $this->assertEquals($sendWeekly[0], true);
        $this->MailingList->delete($mailingListWeekly);
    }
}
