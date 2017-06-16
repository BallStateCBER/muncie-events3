<?php
namespace App\Test\TestCase\Controller;

use App\Controller\UsersController;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestCase;

/**
 * App\Controller\UsersController Test Case
 */
class UsersControllerTest extends IntegrationTestCase
{
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
     * Test registration
     *
     * @return void
     */
    public function testRegistrationFormCorrectly()
    {
        $this->get('/register');

        $this->assertResponseOk();

        $data = [
            'name' => 'Mal Blum',
            'password' => 'letstopcheatingoneachother',
            'confirm_password' => 'letstopcheatingoneachother',
            'email' => 'mal@blum.com'
        ];

        $this->post('/register', $data);

        $this->assertResponseSuccess();
    }

    /**
     * Test registration
     * when the registration form is filled out completely wrong
     *
     * @return void
     */
    public function testRegistrationFormIncorrectly()
    {
        $this->get('/register');

        $data = [
            'name' => 'Mal Blum',
            'password' => 'letstopcheatingoneachother',
            'confirm_password' => 'imatotallydifferentpassword',
            'email' => 'not an email'
        ];

        $this->post('/register', $data);

        $this->assertResponseContains('Your passwords do not match');

        // currently, only front-end validation is happening, so this will fail...
        $this->assertResponseContains('Email must be a valid email address');
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
}
