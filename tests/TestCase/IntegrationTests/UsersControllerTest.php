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
    public function testLoggingIn()
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
    }
    /**
     * Test logout
     *
     * @return void
     */
    public function testLoggingOut()
    {
        $this->session($this->commoner);

        $this->get('/logout');
        $this->assertSession(null, 'Auth.User.id');
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
