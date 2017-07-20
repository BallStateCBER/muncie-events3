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
        $this->MailingList = TableRegistry::get('MailingList');
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
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
        $daily = $this->MailingList->getDailyRecipients();
        debug($daily);
    }
}
