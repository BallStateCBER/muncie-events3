<?php
namespace App\Test\TestCase\Controller;

use App\Test\TestCase\ApplicationTest;

class UsersControllerTest extends ApplicationTest
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
     * Test login method
     *
     * @return void
     */
    public function testLoggingInAndViewingUsers()
    {
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->get('/login');
        $this->assertResponseOk();

        $data = [
            'email' => 'userplaceholder@bsu.edu',
            'password' => 'i am such a great password'
        ];

        $this->post('/login', $data);

        $this->assertResponseContains('We could not log you in.');

        $this->get('/login');
        $this->assertResponseOk();

        $data = [
            'email' => 'userplaceholder@bsu.edu',
            'password' => 'placeholder'
        ];

        $this->post('/login', $data);

        $id = $this->Users->getIdFromEmail('userplaceholder@bsu.edu');
        $this->session(['Auth.User.id' => $id]);

        $this->get('/user/1');
        $this->assertResponseContains('adminplaceholder@bsu.edu');
    }

    /**
     * Test logout
     *
     * @return void
     */
    public function testLoggingOutAndViewingUsers()
    {
        $this->session($this->commoner);

        $this->get('/logout');
        $this->assertSession(null, 'Auth.User.id');

        $this->get('/user/1');
        $this->assertResponseContains('to view email address.');
    }

    /**
     * Test the procedure for resetting one's password
     *
     * @return void
     */
    public function testPasswordResetProcedure()
    {
        $this->get('/users/forgot-password');
        $this->assertResponseOk();

        $user = [
          'email' => 'adminplaceholder@bsu.edu'
        ];
        $this->post('/users/forgot-password', $user);
        $this->assertResponseContains('Message sent.');
        $this->assertResponseOk();

        $this->get('/users/reset-password/1/12345');
        $this->assertRedirect('/');

        // get password reset hash
        $hash = $this->Users->getResetPasswordHash(1, 'adminplaceholder@bsu.edu');
        $resetUrl = "/users/reset-password/1/$hash";
        $this->get($resetUrl);
        $this->assertResponseOk();

        $passwords = [
            'new_password' => 'Placeholder!',
            'new_confirm_password' => 'Placeholder!'
        ];
        $this->post($resetUrl, $passwords);
        $this->assertResponseContains('Password changed.');
        $this->assertResponseOk();

        $this->get('/login');

        $newCreds = [
            'email' => 'adminplaceholder@bsu.edu',
            'password' => 'Placeholder!'
        ];

        $this->post('/login', $newCreds);
        $this->assertSession(1, 'Auth.User.id');
    }

    /**
     * Tests that the registration page loads from a GET request
     *
     * @return void
     */
    public function testRegistrationGet()
    {
        $this->get('/register');
        $this->assertResponseOk();
    }

    /**
     * Tests that registration rejects passwords that don't match
     *
     * @return void
     */
    public function testRegistrationUnmatchedPasswords()
    {
        $newUser = [
            'name' => 'Paulie Placeholder',
            'password' => 'placeholder',
            'confirm_password' => 'placehollder',
            'email' => 'userplaceholder2@example.com'
        ];

        $this->post('/register', $newUser);
        $this->assertResponseContains('Your passwords do not match.');
    }

    /**
     * Tests that registration with valid data works
     *
     * @return void
     */
    public function testRegistrationPost()
    {
        $newUser = [
            'name' => 'Paulie Placeholder',
            'password' => 'placeholder',
            'confirm_password' => 'placeholder',
            'email' => 'userplaceholder2@example.com'
        ];

        $this->post('/register', $newUser);
        $this->assertRedirect('/account');
        $this->assertSession(3, 'Auth.User.id');
    }

    public function testDeleteUser()
    {
        $this->session($this->admin);

        $userId = 2;
        $this->get("users/delete/$userId");
        $this->assertRedirect('/');

        $result = $this->Users->find()
            ->where(['id' => $userId])
            ->count();
        $this->assertEquals(0, $result);
    }
}
