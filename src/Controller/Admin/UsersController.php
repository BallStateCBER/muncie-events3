<?php
namespace App\Controller\Admin;

use App\Controller\AppController;
use App\Model\Entity\User;

/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 */
class UsersController extends AppController
{
    /**
     * Initialization hook method.
     *
     * @return void
     * @throws \Exception
     */
    public function initialize()
    {
        parent::initialize();
        $this->Auth->deny();
    }

    /**
     * Determines whether or not the user is authorized to make the current request
     *
     * @param User|null $user User entity
     * @return bool
     */
    public function isAuthorized($user)
    {
        return ($user['role'] == 'admin');
    }

    /**
     * moderate function for checking users
     *
     * @return null
     */
    public function moderate()
    {
        $this->set('titleForLayout', 'Moderate new users');
        $users = $this->Users->getRecentUsers();
        $this->set(compact('users'));

        return null;
    }

    /**
     * setUserAsSpam function for setting users as spam
     *
     * @param int $id of spam
     * @return null
     */
    public function setUserAsSpam($id)
    {
        $this->autoRender = false;
        $user = $this->Users->get($id);
        if ($this->Users->setUserAsSpam($user)) {
            $this->Flash->success('The user has been marked as spam.');

            return $this->redirect(['action' => 'moderate']);
        }
        $this->Flash->error('The user could not be marked as spam. Please, try again.');

        return $this->redirect(['action' => 'moderate']);
    }

    /**
     * deleting users
     *
     * @param int|null $id of the user to delete
     * @return \Cake\Http\Response;
     */
    public function delete($id = null)
    {
        $this->autoRender = false;
        $user = $this->Users->get($id);
        if ($this->Users->delete($user)) {
            $this->Flash->success('The user has been deleted.');

            return $this->redirect($this->getRequest()->referer());
        }
        $this->Flash->error('The user could not be deleted. Please, try again.');

        return $this->redirect(['action' => 'index']);
    }
}
