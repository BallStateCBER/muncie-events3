<?php
namespace App\Test\TestCase\Controller;

use App\Controller\MailingListController;
use Cake\TestSuite\IntegrationTestCase;
use Facebook\FacebookSession;
use Facebook\FacebookRedirectLoginHelper;

/**
 * App\Controller\MailingListController Test Case
 */
class MailingListControllerTest extends IntegrationTestCase
{
    public function setUp()
    {
        parent::setUp();

        $id = '496726620385625';
        $secret = '8c2bca1961dbf8c8bb92484d9d2dd318';
        FacebookSession::setDefaultApplication($id, $secret);

        $redirectUrl = '/users/login';
        $helper = new FacebookRedirectLoginHelper($redirectUrl);
        $helper->disableSessionStatusCheck();
    }

    /**
     * Test index method
     *
     * @return void
     */
    public function testIndex()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test view method
     *
     * @return void
     */
    public function testView()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test add method
     *
     * @return void
     */
    public function testAdd()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test edit method
     *
     * @return void
     */
    public function testEdit()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test delete method
     *
     * @return void
     */
    public function testDelete()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
