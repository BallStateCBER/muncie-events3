<?php
namespace App\Test\TestCase\Controller;

use App\Controller\UsersController;
use Cake\ORM\TableRegistry;
use App\Test\TestCase\AppControllerTest;

/**
 * App\Controller\UsersController Test Case
 */
class UsersViewTest extends AppControllerTest
{
    public function setUp()
    {
        parent::setUp();
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
        $this->get('/user/221');

        $this->assertResponseOk();
        $this->assertResponseContains('to view email address.');
    }

    /**
     * Test view method
     * when you're logged in
     *
     * @return void
     */
    public function testViewWhenLoggedIn()
    {
        $this->session(['Auth.User.id' => 1]);
        $this->get('/user/221');

        $this->assertResponseOk();
        $this->assertResponseContains('theadoptedtenenbaum@gmail.com');
    }

    /**
     * Test view method
     * when the user actually has account details
     *
     * @return void
     */
    public function testViewWithBioAndEvents()
    {
        $this->get('/user/1');

        $this->assertResponseOk();
        $this->assertResponseContains('Bio');
        $this->assertResponseContains('Thursday');
    }
}
