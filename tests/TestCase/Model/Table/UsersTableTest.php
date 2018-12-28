<?php
namespace App\Test\TestCase\Controller;

use App\Test\TestCase\ApplicationTest;
use Cake\Core\Configure;

class UsersTableTest extends ApplicationTest
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\UsersTable
     */
    public $Users;
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
     * Test getEmailFromId method
     *
     * @return void
     */
    public function testGetEmailFromId()
    {
        $user = $this->Users->find()
            ->where(['name' => 'Stevie User'])
            ->first();
        $email = $this->Users->getEmailFromId($user->id);
        $this->assertEquals($user['email'], $email);
    }
    /**
     * Test getIdFromEmail method
     *
     * @return void
     */
    public function testGetIdFromEmail()
    {
        $user = $this->Users->find()
            ->where(['name' => 'Stevie User'])
            ->first();
        $id = $this->Users->getIdFromEmail($user['email']);
        $this->assertEquals($user->id, $id);
    }
    /**
     * Test getResetPasswordHash method
     *
     * @return void
     */
    public function testGetResetPasswordHash()
    {
        $user = $this->Users->find()
            ->where(['name' => 'Stevie User'])
            ->first();
        $hash = $this->Users->getResetPasswordHash($user->id, $user['email']);
        $this->assertEquals(md5($user->id . $user['email'] . Configure::read('password_reset_salt') . date('my')), $hash);
    }
    /**
     * Test sendPasswordResetEmail
     *
     * @return void
     */
    public function testSendPasswordResetEmail()
    {
        $user = $this->Users->find()
            ->where(['name' => 'Stevie User'])
            ->first();
        $email = $this->Users->sendPasswordResetEmail($user->id, $user['email']);
        $email = implode($email);
        $resetPasswordHash = $this->Users->getResetPasswordHash($user->id, $user['email']);
        $this->assertTextContains($resetPasswordHash, $email);
    }
    /**
     * Test getImagesList
     *
     * @return void
     */
    public function testGetImagesList()
    {
        $images = $this->Users->getImagesList(1);
        $this->assertEquals($images[0]->user_id, 1);
    }
}
