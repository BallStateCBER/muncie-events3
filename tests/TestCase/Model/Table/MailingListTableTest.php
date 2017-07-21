<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\MailingListTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\MailingListTable Test Case
 */
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
        $this->Categories = TableRegistry::get('Categories');
        $this->Events = TableRegistry::get('Events');
        $this->MailingList = TableRegistry::get('MailingList');
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Categories);
        unset($this->Events);
        unset($this->MailingList);

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
        $weekday  = $this->MailingList->getWeeklyDeliveryDay();
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
            ->where(['email' => 'placeholder@gmail.com'])
            ->first();

        $hash = $this->MailingList->getHash($mailingListWeekly->id);
        $secondHash = md5('recipient'.$mailingListWeekly->id);

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
        $this->assertEquals($processed->new_subscriber, 0);

        $this->MailingList->delete($mailingListDaily);
    }

    /**
     * Test setWeeklyAsProcessed method
     *
     * @return void
     */
    public function testSetWeeklyAsProcessed()
    {
        $mailingListWeekly = $this->MailingList->find()
            ->where(['email' => 'placeholder@gmail.com'])
            ->first();

        $processed = $this->MailingList->setWeeklyAsProcessed($mailingListWeekly->id, 0);
        $this->assertEquals($processed->new_subscriber, 0);
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
     * Test filterWeeksEvents method
     *
     * @return void
     */
    public function testFilterWeeksEvents()
    {
        /*    $mailingListWeekly = $this->MailingList->find()
            ->where(['email' => 'placeholder@gmail.com'])
            ->first();
        $mailingListWeekly->all_categories = 0;
        $this->MailingList->save($mailingListWeekly);

        $general = $this->Categories->get(13);
        $this->MailingList->Categories->link($mailingListWeekly, [$general]);

        debug($mailingListWeekly->Categories);

        $events = $this->Events->find()
            ->contain(['Categories', 'EventSeries', 'Images', 'Tags', 'Users'])
            ->where(['date >=' => date('Y-m-d')])
            ->andWhere(['date <=' => date('Y-m-d', strtotime('+1 week'))])
            ->toArray();

        $events = $this->MailingList->filterWeeksEvents($mailingListWeekly, $events);
        debug($events);
        foreach ($events as $event) {
            $this->assertEquals(13, $event->category_id);
        }

        $this->MailingList->delete($mailingListWeekly); */
    }
}
