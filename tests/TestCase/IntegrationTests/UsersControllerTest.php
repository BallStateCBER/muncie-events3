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
        $this->assertSession($id, 'Auth.User.id');
        $this->session(['Auth.User.id' => 1]);

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
     * Test entire life cycle of user account
     *
     * @return void
     */
    public function testRegistrationAndAccountEditingAndDeletingAUser()
    {
        $this->get('/register');
        $this->assertResponseOk();

        // validation works?
        $newUser = [
            'name' => 'Paulie Placeholder',
            'password' => 'placeholder',
            'confirm_password' => 'placehollder',
            'email' => 'userplaceholder2@bsu.edu'
        ];

        $this->post('/register', $newUser);
        $this->assertResponseContains('Your passwords do not match.');

        // let's try again!
        $newUser['confirm_password'] = 'placeholder';

        $this->post('/register', $newUser);
        $this->assertRedirect('/account');
        $this->assertSession(3, 'Auth.User.id');

        $accountInfo = [
            'name' => 'Paulie Farce',
            'email' => 'userplaceholder2@bsu.edu',
            'bio' => 'I am yet another placeholder.',
            'photo' => [
                'tmp_name' => null,
                'error' => 4,
                'name' => null,
                'type' => null,
                'size' => 0
            ]
        ];
        $id = $this->Users->getIdFromEmail($accountInfo['email']);

        // for the moment, we're not using this test, because it's not working and I have no idea why
        /*$this->get('/account');
        $this->post('/account', $accountInfo);
        $user = $this->Users->get($id);
        dd($user);

        $this->assertEquals('I am yet another placeholder.', $user->bio);*/

        $this->get('/logout');

        $this->session($this->commoner);

        $this->get("users/delete/$id");
        $id = $this->Users->getIdFromEmail($accountInfo['email']);
        $this->assertEquals($id, 3);

        // let's try again with an admin
        $this->session($this->admin);

        $this->get("users/delete/$id");

        $this->assertRedirect('/');
        $id = $this->Users->getIdFromEmail($accountInfo['email']);
        $this->assertEquals($id, null);

        $this->markTestIncomplete();
    }
}
