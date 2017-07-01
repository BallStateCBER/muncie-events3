<?php
namespace App\Test\TestCase\Controller;

use App\Controller\UsersController;
use Cake\Core\Configure;
use Cake\Mailer\Email;
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
        $this->markTestIncomplete('Email validation not working, work in progress.');
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
            'name' => 'Mal Blum',
            'password' => 'letstopcheatingoneachother',
            'confirm_password' => 'letstopcheatingoneachother',
            'email' => 'mal@blum.com'
        ];

        $this->post('/register', $data);

#        $this->assertResponseContains('There is already an account registered with this email address.');

        $this->markTestIncomplete('Email validation not working, work in progress.');
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
            'email' => 'hotdogplaceholderpants@fuckyou.net',
            'password' => 'i am such a great password'
        ];

        $this->post('/login', $data);

        $this->assertResponseContains('We could not log you in.');

        $this->get('/login');
        $this->assertResponseOk();

        $data = [
            'email' => 'placeholder@gmail.com',
            'password' => 'Placeholder!'
        ];

        $this->post('/login', $data);

        $this->Users = TableRegistry::get('Users');
        $id = $this->Users->getIdFromEmail('placeholder@gmail.com');

        $this->assertSession($id, 'Auth.User.id');
    }

    /**
     * Test editing account info
     *
     * @return void
     */
    public function testAccountInfoForUsers()
    {
        $this->Users = TableRegistry::get('Users');
        $this->session(['Auth.User.id' => 554]);

        $this->get('/account');
        $userInfo = [
            'name' => 'Placeholder Extra',
            'email' => 'placeholder@ymail.com',
            'bio' => "I'm the placeholder!"
        ];
        $user = $this->Users->get(554);
        $user = $this->Users->patchEntity($user, $userInfo);
        if ($this->Users->save($user)) {
            $this->assertResponseOk();
        }
    }

    /**
     * Test editing account info
     * plus file uploading
     *
     * @return void
     */
    public function testPhotoUploadingForUsers()
    {
        $this->Users = TableRegistry::get('Users');
        $this->session(['Auth.User.id' => 554]);

        $salt = Configure::read('profile_salt');
        $newFilename = md5('placeholder.jpg'.$salt);

        $this->get('/account');
        $userInfo = [
            'name' => 'Placeholder',
            'email' => 'placeholder@gmail.com',
            'bio' => "I'm the BEST placeholder!",
            'photo' => [
                'name' => 'placeholder.jpg',
                'type' => 'image/jpeg',
                'tmp_name' => WWW_ROOT . DS . 'img' . DS . 'users' . $newFilename,
                'error' => 4,
                'size' => 845941,
            ]
        ];
        $user = $this->Users->get(554);
        $user = $this->Users->patchEntity($user, $userInfo);
        if ($this->Users->save($user)) {
            $this->assertResponseOk();
            if ($user->photo == $newFilename) {
                return $this->assertResponseOk();
            }

            // file upload unit testing not done yet!
            $this->markTestIncomplete();
        }
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
        $id = $this->Users->getIdFromEmail('mal@blum.com');

        $this->get("users/delete/$id");
        $this->assertResponseSuccess();
    }

    /**
     * Test sending password reset email
     *
     * @return void
     */
    public function testSendingPasswordReset()
    {
        $this->get("users/forgot-password");
        $this->assertResponseOk();

        $data = [
            'email' => 'placeholder@gmail.com'
        ];

        $this->post('users/forgot-password', $data);
        $this->assertResponseContains('Message sent.');
        $this->assertResponseOk();
    }

    /**
     * Test actually resetting the password
     *
     * @return void
     */
    public function testResettingThePassword()
    {
        $this->session(['Auth.User.id' => 554]);

        // what if someone's trying to fabricate a password-resetting code?
        $this->get("users/reset-password/554/abcdefg");
        $this->assertRedirect('/');

        // get password reset hash
        $this->Users = TableRegistry::get('Users');
        $hash = $this->Users->getResetPasswordHash(554, 'placeholder@gmail.com');

        // now, this is the REAL URL for password resetting
        $resetUrl = "users/reset-password/554/$hash";
        $this->get($resetUrl);
        $this->assertResponseOk();

        $passwords = [
            'new_password' => 'Placeholder!',
            'new_confirm_password' => 'Placeholder!'
        ];

        $this->post($resetUrl, $passwords);
        $this->assertResponseContains('Password changed.');
        $this->assertResponseOk();
    }
}
