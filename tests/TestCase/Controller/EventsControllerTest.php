<?php
namespace App\Test\TestCase\Controller;

use App\Controller\EventsController;
use Cake\TestSuite\IntegrationTestCase;

/**
 * App\Controller\EventsController Test Case
 */
class EventsControllerTest extends IntegrationTestCase
{
    /*
     * test event add page when logged out
     */
    public function testLoginRedirectWhenAddingEvents()
    {
        $this->get('/events/add');
        $this->assertRedirectContains('/login');
    }

    /*
     * test that users' email addresses are hidden from non-users
     */
    public function testUsersHaveHiddenEmailsFromNonUsers()
    {
        $this->get('/user/1');
        $this->assertResponseContains('to view email address.');
    }
}
