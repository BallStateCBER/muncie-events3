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
                if ($this->request->data('auto_login')) {
                    $this->Cookie->configKey('CookieAuth', [
                        'expires' => '+1 year',
                        'httpOnly' => true
                    ]);
                    $this->Cookie->write('CookieAuth', [
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
    public function logout()
    {
        return $this->redirect($this->Auth->logout());
    }

    public function register()
    {
        $this->set('titleForLayout', 'Register');

        $user = $this->Users->newEntity();

        if ($this->request->is('post')) {
            $user = $this->Users->patchEntity($user, $this->request->getData());
            $user['email'] = strtolower(trim($user['email']));
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
        $this->set('titleForLayout', 'Your Account');

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
        $user = $this->Users->newEntity();

        if ($this->request->is('post')) {
            $adminEmail = Configure::read('admin_email');
            $user = $this->Users->patchEntity($user, $this->request->getData());
            $email = strtolower(trim($user['email']));
            if (!empty($email)) {
                $userId = $this->Users->getIdFromEmail($email);
                if ($userId) {
                    if ($this->Users->sendPasswordResetEmail($userId, $email)) {
                        $this->Flash->success('Message sent. You should be shortly receiving an email with a link to reset your password.');
                        return $this->redirect('/');
                    }
                    $this->Flash->error('Whoops. There was an error sending your password-resetting email out. Please try again, and if it continues to not work, email <a href="mailto:'.$adminEmail.'">'.$adminEmail.'</a> for assistance.');
                }
                if (!$userId) {
                    $this->Flash->error('We couldn\'t find an account registered with the email address '.$email.'.');
                }
            }
            if (empty($email)) {
                $this->Flash->error('Please enter the email address you registered with to have your password reset.');
            }
        }
    }

    public function resetPassword($userId, $resetPasswordHash)
    {
        $email = $this->Users->getEmailFromId($userId);
        $user = $this->Users->get($userId);

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
                $this->Flash->success('Password changed. You are now logged in.');
                return $this->redirect('/');
            }
            $this->Flash->error('There was an error changing your password. Please check to make sure they\'ve been entered correctly.');
            return $this->redirect('/');
        }
    }

    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $user = $this->Users->get($id);
        if ($this->Users->delete($user)) {
            $this->Flash->success(__('The user has been deleted.'));
            return $this->redirect('/');
        }
        $this->Flash->error(__('The user could not be deleted. Please, try again.'));
        return $this->redirect(['action' => 'index']);
    }

    public function search()
    {
        $filter= $this->request->query;

        // Determine the direction (past or future)
        $direction = $filter['direction'];

        $dateQuery = ($direction == 'future') ? 'date >=' : 'date <';
        if ($direction == 'all') {
            $dateQuery = 'date !=';
        };
        $dir = ($direction == 'future') ? 'ASC' : 'DESC';
        $dateWhen = ($direction == 'all') ? '1900-01-01' : date('Y-m-d');

        $events = $this->Events->find('search', [
            'search' => $filter,
            'contain' => ['Users', 'Categories', 'EventSeries', 'Images', 'Tags']])
            ->where([$dateQuery => $dateWhen])
            ->order(['date' => $dir])
            ->toArray();

        if ($events) {
            $this->indexEvents($events);
        }

        if ($direction == 'all') {
            $currentDate = date('Y-m-d');
            $counts = ['upcoming' => 0, 'past' => 0];
            foreach ($events as $date => $dateEvents) {
                if ($date >= $currentDate) {
                    $counts['upcoming']++;
                }
                if ($date < $currentDate) {
                    $counts['past']++;
                }
            }
            $this->set(compact('counts'));
        }
        if ($direction == 'past' || $direction = 'future') {
            // Determine if there are events in the opposite direction
            $this->passedArgs['direction'] = ($direction == 'future') ? 'past' : 'future';
            if ($this->passedArgs['direction'] == 'past') {
                $oppositeCount = $this->Events->find('search', [
                    'search' => $filter])
                    ->where(['date <' => date('Y-m-d')])
                    ->count();
            } elseif ($this->passedArgs['direction'] == 'future') {
                $oppositeCount = $this->Events->find('search', [
                    'search' => $filter])
                    ->where(['date >=' => date('Y-m-d')])
                    ->count();
            }
            $this->set('oppositeEvents', $oppositeCount);
        }

        $tags = $this->Events->Tags->find('search', [
            'search' => $filter]);
        $tagCount = null;
        foreach ($tags as $tag) {
            if ($tag-> id) {
                $tagCount = true;
            }
        }

        $this->set([
            'titleForLayout' => 'Search Results',
            'direction' => $direction,
            'directionAdjective' => ($direction == 'future') ? 'upcoming' : $direction,
            'filter' => $filter,
            'dateQuery' => $dateQuery,
            'tags' => $tags,
            'tagCount' => $tagCount
        ]);
    }
}
