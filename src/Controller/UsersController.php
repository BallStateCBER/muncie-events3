<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Routing\Router;
use Cake\I18n\Date;
use Cake\I18n\Time;

/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 */
class UsersController extends AppController
{
    public function initialize()
    {
        parent::initialize();
        // we should probably allow people to register & change their passwords
        // I *guess*.
        $this->Auth->allow([
            'register', 'forgotPassword', 'resetPassword', 'view'
        ]);
    }

    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);
    }

    public function view($id = null)
    {
        $user = $this->Users->get($id, [
            'contain' => ['MailingList', 'EventSeries', 'Events', 'Images', 'Tags']
        ]);

        $eventCount = $this->Users->Events
            ->find('all', [
            'conditions' => ['user_id' => $id]
            ])
            ->count();

        $events = $this->Users->Events
            ->find('all', [
            'contain' => ['Categories', 'EventSeries', 'Images', 'Tags'],
            'order' => ['date' => 'DESC']
            ])
            ->where([
                'Events.user_id =' => $id
            ])
            ->toArray();

        $this->indexEvents($events);

        $this->set([
            'eventCount' => $eventCount,
            'titleForLayout' => $user->name,
            'user' => $user
        ]);
        $this->set('_serialize', ['user']);
    }


    public function login()
    {
        $this->set('titleForLayout', 'Log In');
        if ($this->request->is('post')) {
            $user = $this->Auth->identify();
            if ($user) {
                $this->Auth->setUser($user);

                // do they have an old sha1 password?
                if ($this->Auth->authenticationProvider()->needsPasswordRehash()) {
                    $user = $this->Users->get($this->Auth->user('id'));
                    $user->password = $this->request->getData('password');
                    $this->Users->save($user);
                }

                // Remember login information
                if ($this->request->data('remember_me')) {
                    $this->Cookie->configKey('User', [
                        'expires' => '+1 year',
                        'httpOnly' => true
                    ]);
                    $this->Cookie->write('User', [
                        'email' => $this->request->data('email'),
                        'password' => $this->request->data('password')
                    ]);
                }
                return $this->redirect($this->Auth->redirectUrl());
            }
            if (!$user) {
                $this->Flash->error(__('We could not log you in. Please check your email & password.'));
            }
        }
    }

    /**
     * Intercepts failed Facebook logins
     */
    public function confirm_facebook_login()
    {
        /*
         * THIS IS APPARENTLY NOT DOING WHAT IT'S SUPPOSED TO.
         * $this->Auth->user('id') is coming up null even when the user
         * is successfully logged in.
         */

        // User was successfully logged in
        if (true || $this->Auth->user('id')) {
            $this->redirect('/');
        }

        // User was not logged in
        //$this->Flash->error('There was an error logging you in via Facebook. Make sure that you have registered an account with Facebook or synced an account with Facebook before trying to log in to it.');
        $this->redirect($this->referer());
    }

    public function logout()
    {
        $this->Flash->success('Thanks for stopping by!');
        return $this->redirect($this->Auth->logout());
    }

    public function register()
    {
        $this->set('titleForLayout', 'Register');

        $user = $this->Users->newEntity();

        if ($this->request->is('post')) {
            $user = $this->Users->patchEntity($user, $this->request->getData());
            $user['email'] = strtolower($user['email']);
            if ($this->Users->save($user)) {
                $this->Flash->success(__('Success! You have been registered.'));
                $this->Auth->setUser($user);
                return $this->redirect(['action' => 'account']);
            }
            $this->Flash->error(__('Sorry, we could not register you. Please try again.'));
        }

        #$mailingLists = $this->Users->MailingList->find('list', ['limit' => 200]);
        $this->set(compact('user'));
        $this->set('_serialize', ['user']);
    }

    public function account()
    {
        $this->set('titleForLayout', 'My Account');

        $id = $this->Auth->user('id');
        $email = $this->Auth->user('email');
        $user = $this->Users->get($id);

        $resetPasswordHash = $this->Users->getResetPasswordHash($id, $email);

        $resetUrl = Router::url([
            'controller' => 'users',
            'action' => 'resetPassword',
            $id,
            $resetPasswordHash
        ], true);

        $this->set('reset_url', $resetUrl);

        if ($this->request->is(['patch', 'post', 'put'])) {
            $user = $this->Users->patchEntity($user, $this->request->getData());
            if ($this->Users->save($user)) {
                $data = $user->toArray();
                $this->Auth->setUser($data);
                $this->Flash->success(__('Your account has been updated.'));
                return $this->redirect('/');
            }
            $this->Flash->error(__('Sorry, we could not update your information. Please try again.'));
        }
        $this->set(compact('user'));
    }

    public function forgotPassword()
    {
        $this->set([
            'titleForLayout' => 'Forgot Password'
        ]);

        if ($this->request->is('post')) {
            $adminEmail = Configure::read('admin_email');
            $email = strtolower(trim($this->request->data['email']));
            $userId = $this->Users->getIdFromEmail($email);
            if ($userId) {
                if ($this->Users->sendPasswordResetEmail($userId, $email)) {
                    return $this->Flash->success('Message sent. You should be shortly receiving an email with a link to reset your password.');
                }
                $this->Flash->error('Whoops. There was an error sending your password-resetting email out. Please try again, and if it continues to not work, email <a href="mailto:'.$adminEmail.'">'.$adminEmail.'</a> for assistance.');
            }
            if (!$userId) {
                $this->Flash->error('We couldn\'t find an account registered with the email address '.$email.'.');
            }

            if (!isset($email)) {
                $this->Flash->error('Please enter the email address you registered with to have your password reset.');
            }
        }
    }

    public function resetPassword($userId, $resetPasswordHash)
    {
        $user = $this->Users->get($userId);
        $email = $user->email;

        $this->set([
            'titleForLayout' => 'Reset Password',
            'user_id' => $userId,
            'email' => $email,
            'reset_password_hash' => $resetPasswordHash
        ]);

        $expectedHash = $this->Users->getResetPasswordHash($userId, $email);

        if ($resetPasswordHash != $expectedHash) {
            $this->Flash->error('Invalid password-resetting code. Make sure that you entered the correct address and that the link emailed to you hasn\'t expired.');
            $this->redirect('/');
        }

        if ($this->request->is('post')) {
            $user = $this->Users->patchEntity($user, [
                'password' => $this->request->data['new_password'],
                'confirm_password' => $this->request->data['new_confirm_password']
            ]);

            if ($this->Users->save($user)) {
                $data = $user->toArray();
                $this->Auth->setUser($data);
                return $this->Flash->success('Password changed. You are now logged in.');
            }
            $this->Flash->error('There was an error changing your password. Please check to make sure they\'ve been entered correctly.');
            return $this->redirect('/');
        }
    }

    public function delete($id = null)
    {
        $this->autoRender = false;
        $user = $this->Users->get($id);
        if ($this->Users->delete($user)) {
            $this->Flash->success(__('The user has been deleted.'));
            return $this->redirect('/');
        }
        $this->Flash->error(__('The user could not be deleted. Please, try again.'));
        return $this->redirect(['action' => 'index']);
    }
}
