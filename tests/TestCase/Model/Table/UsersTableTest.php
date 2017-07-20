<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\UsersTable;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\UsersTable Test Case
 */
class UsersTableTest extends TestCase
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
        $config = TableRegistry::exists('Users') ? [] : ['className' => 'App\Model\Table\UsersTable'];
        $this->Users = TableRegistry::get('Users', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Users);

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
            ->where(['name' => 'Placeholder'])
            ->first();

        $email = $this->Users->getEmailFromId($user->id);

        $this->assertEquals($user->email, $email);
    }

    /**
     * Test getIdFromEmail method
     *
     * @return void
     */
    public function testGetIdFromEmail()
    {
        $user = $this->Users->find()
            ->where(['name' => 'Placeholder'])
            ->first();

        $id = $this->Users->getIdFromEmail($user->email);

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
            ->where(['name' => 'Placeholder'])
            ->first();

        $hash = $this->Users->getResetPasswordHash($user->id, $user->email);

        $this->assertEquals(md5($user->id.$user->email.Configure::read('password_reset_salt').date('my')), $hash);
    }

    /**
     * Test sendPasswordResetEmail
     *
     * @return void
     */
    public function testSendPasswordResetEmail()
    {
        $user = $this->Users->find()
            ->where(['name' => 'Placeholder'])
            ->first();

        $email = $this->Users->sendPasswordResetEmail($user->id, $user->email);
        $email = implode($email);

        $resetPasswordHash = $this->Users->getResetPasswordHash($user->id, $user->email);

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
