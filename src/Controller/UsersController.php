<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Routing\Router;

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
            'register', 'forgotPassword', 'resetPassword'
        ]);
    }

    public function index()
    {
        $this->paginate = [
            'contain' => ['MailingList']
        ];
        $users = $this->paginate($this->Users);

        $this->set(compact('users'));
        $this->set('_serialize', ['users']);
    }

    public function view($id = null)
    {
        $user = $this->Users->get($id, [
            'contain' => ['MailingList', 'EventSeries', 'Events', 'Images', 'Tags']
        ]);

        $this->set('user', $user);
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
            } else {
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
        $this->set('titleForLayout', 'Register');;

        $user = $this->Users->newEntity();

        if ($this->request->is('post')) {
            $user = $this->Users->patchEntity($user, $this->request->getData());
            $user['email'] = strtolower(trim($user['email']));
            if ($this->Users->save($user)) {
                $this->Flash->success(__('The user has been saved.'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('Sorry, we could not register you. Please try again.'));
        }

        $mailingLists = $this->Users->MailingList->find('list', ['limit' => 200]);
        $this->set(compact('user'));
        $this->set('_serialize', ['user']);
    }

    public function account()
    {
        $this->set('titleForLayout', 'Your Account');

        $id = $this->Auth->user('id');
        $email = $this->Auth->user('email');
        $user = $this->Users->get($id);

        $reset_password_hash = $this->Users->getResetPasswordHash($id, $email);

        $reset_url = Router::url([
            'controller' => 'users',
            'action' => 'resetPassword',
            $id,
            $reset_password_hash
        ], true);

        $this->set('reset_url', $reset_url);

        if ($this->request->is(['post'])) {
            $user = $this->Users->patchEntity($user, $this->request->getData());
            if ($this->Users->save($user)) {
                $data = $user->toArray();
                $this->Auth->setUser($data);
                $this->Flash->success(__('The user has been saved.'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('Sorry, we could not update your information. Please try again.'));
        }
    }

    public function forgotPassword() {
        $this->set([
            'titleForLayout' => 'Forgot Password'
        ]);
        $user = $this->Users->newEntity();

        if ($this->request->is('post')) {
            $admin_email = Configure::read('admin_email');
            $user = $this->Users->patchEntity($user, $this->request->getData());
            $email = strtolower(trim($user['email']));
            if (empty($email)) {
                $this->Flash->error('Please enter the email address you registered with to have your password reset.');
            } else {
                $user_id = $this->Users->getIdFromEmail($email);
                if ($user_id) {
                    if ($this->Users->sendPasswordResetEmail($user_id, $email)) {
                        $this->Flash->success('Message sent. You should be shortly receiving an email with a link to reset your password.');
                    } else {
                        $this->Flash->error('Whoops. There was an error sending your password-resetting email out. Please try again, and if it continues to not work, email <a href="mailto:'.$admin_email.'">'.$admin_email.'</a> for assistance.');
                    }
                } else {
                    $this->Flash->error('We couldn\'t find an account registered with the email address '.$email.'.');
                }
            }
        }
    }

    public function resetPassword($user_id, $reset_password_hash) {
        $email = $this->Users->getEmailFromId($user_id);
        $user = $this->Users->get($user_id);

        $this->set([
            'titleForLayout' => 'Reset Password',
            'user_id' => $user_id,
            'email' => $email,
            'reset_password_hash' => $reset_password_hash
        ]);

        $expected_hash = $this->Users->getResetPasswordHash($user_id, $email);

        if ($reset_password_hash != $expected_hash) {
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
                $this->redirect('/');
            } else {
                $this->Flash->error('There was an error changing your password. Please check to make sure they\'ve been entered correctly.');
            }
        }
    }

    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $user = $this->Users->get($id);
        if ($this->Users->delete($user)) {
            $this->Flash->success(__('The user has been deleted.'));
        } else {
            $this->Flash->error(__('The user could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
