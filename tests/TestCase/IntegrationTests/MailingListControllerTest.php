<?php
namespace App\Test\TestCase\Controller;

use App\Test\TestCase\ApplicationTest;

class MailingListControllerTest extends ApplicationTest
{
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
     * test sendDaily method
     *
     * @return void
     */
    public function testSendDaily()
    {
        $this->session($this->admin);
        $this->get('/mailing-list/send-daily');
        $this->assertResponseOk();
    }

    /**
     * test sendWeekly method
     *
     * @return void
     */
    public function testSendWeekly()
    {
        $this->session($this->admin);
        $this->get('/mailing-list/send-weekly');
        $this->assertResponseOk();
    }

    /**
     * test join method
     *
     * @return void
     */
    public function testJoin()
    {
        $this->get('/mailing-list/join');
        $this->assertResponseOk();
    }

    /**
     * test resetProcessedTime method
     *
     * @return void
     */
    public function testResetProcessedTime()
    {
        $this->session($this->admin);
        $this->get('/mailing-list/reset-processed-time');
        $this->assertResponseOk();
    }

    /**
     * test bulkAdd method
     *
     * @return void
     */
    public function testBulkAdd()
    {
        $this->session($this->admin);
        $this->get('/mailing-list/bulk-add');
        $this->assertResponseOk();
    }

    /**
     * test settings method
     *
     * @return void
     */
    public function testSettings()
    {
        $recipientId = $this->dailyMailingList['id'];
        $hash = $this->MailingList->getHash($recipientId);
        $this->get("/mailing-list/settings/$recipientId/$hash");
        $this->assertResponseOk();
    }
}
