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
     * when the registration form is filled out wrong,
     * bad emails, mismatched passwords, etc.
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

        $this->assertResponseContains('Your passwords do not match.');
    #    $this->assertResponseContains('You must enter a valid email address.');
    }

    /**
     * Test registration
     * when the email address is already in use
     *
     * @return void
     */
    public function testRegistrationWithDuplicates()
    {
        $this->get('/register');

        $data = [
            'name' => 'Placeholder',
            'password' => 'Placeholder!',
            'confirm_password' => 'Placeholder!',
            'email' => 'placeholder@gmail.com'
        ];

        $this->post('/register', $data);

#        $this->assertResponseContains('There is already an account registered with this email address.');
    }

    /**
     * Test login method
     *
     * @return void
     */
    public function testLoggingInProperly()
    {
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->get('/login');
        $this->assertResponseOk();

        $data = [
            'email' => 'placeholder@gmail.com',
            'password' => 'Placeholder!'
        ];

        $this->post('/login', $data);

        $this->Users = TableRegistry::get('Users');
        $user = $this->Users->find('all')
            ->where(['email' => 'placeholder@gmail.com'])
            ->first();

        $this->assertSession($user->id, 'Auth.User.id');
    }

    /**
     * Test login method
     * for incorrect logins
     *
     * @return void
     */
    public function testLoggingInImproperly()
    {
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->get('/login');
        $this->assertResponseOk();

        $data = [
            'email' => 'hotdogplaceholderpants@fuckyou.net',
            'password' => 'i am such a great password'
        ];

        $this->post('/login', $data);

        $this->assertResponseContains('We could not log you in.');
    }

    /**
     * Test logout
     *
     * @return void
     */
    public function testLoggingOut()
    {
        $this->session(['Auth.User.id' => 1]);

        $this->get('/logout');
        $this->assertSession(null, 'Auth.User.id');
    }

    /**
     * Test delete action for users
     *
     * @return void
     */
    public function testDeletingUsers()
    {
        $this->session(['Auth.User.id' => 1]);

        // delete the new user
        $this->Users = TableRegistry::get('Users');
        $user = $this->Users->find('all')
            ->where(['email' => 'mal@blum.com'])
            ->first();
        $id = $user->id;

        $this->get("users/delete/$id");
        $this->assertResponseSuccess();
    }
}
