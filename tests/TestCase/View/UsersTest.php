<?php
namespace App\Test\TestCase\Controller;

use App\Controller\UsersController;
use Cake\Http\ServerRequest;
use Cake\Http\Response;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestCase;
use Facebook\FacebookRedirectLoginHelper;

/**
 * App\Controller\UsersController Test Case
 */
class UsersViewTest extends IntegrationTestCase
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
