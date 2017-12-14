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
    #    dd($this->MailingListLog->find()->toArray());
    }

    /**
     * test sendDaily method
     *
     * @return void
     */
    public function testSendWeekly()
    {
        $this->session($this->admin);
        $this->get('/mailing-list/send-weekly');
        $this->assertResponseOk();
    }
}
