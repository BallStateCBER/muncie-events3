<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link      http://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use Cake\Core\Configure;
use Cake\Mailer\Email;
use Cake\Validation\Validator;

/**
 * Static content controller
 *
 * This controller will render views from Template/Pages/
 *
 * @link http://book.cakephp.org/3.0/en/controllers/pages-controller.html
 */
class PagesController extends AppController
{
    /**
     * Initialize hook method.
     *
     * @return void
     */
    public function initialize()
    {
        parent::initialize();
         // you don't need to log in to access pages
         $this->Auth->allow([
             'about', 'contact', 'terms'
         ]);
    }

    /**
     * view for the terms page
     *
     * @return void
     */
    public function about()
    {
        $this->set('titleForLayout', 'About Us');
    }

    /**
     * view for the terms page
     *
     * @return null
     */
    public function contact()
    {
        $this->set('titleForLayout', 'Contact Us');
        $validator = new Validator();
        $validator
            ->requirePresence('name')
            ->notEmpty('name', 'Please tell us who you are.')
            ->requirePresence('email')
            ->notEmpty('email', 'Please provide a valid email address. Otherwise, we can\'t respond back.')
            ->requirePresence('body')
            ->notEmpty('body', 'Don\'t forget to write a message.');
        if ($this->request->is('post')) {
            $this->set($this->request->getData());

            $errors = $validator->errors($this->request->getData());
            if (empty($errors)) {
                $email = new Email('contact_form');
                $email->setFrom([$this->request->getData('email') => $this->request->getData('name')])
                     ->setTo(Configure::read('admin_email'))
                     ->setSubject('Muncie Events contact form: ' . $this->request->getData('category'));
                if ($email->send($this->request->getData('body'))) {
                    $this->Flash->success('Thanks for contacting us. We will try to respond to your message soon.');

                    return null;
                } else {
                    $this->Flash->error('There was some problem sending your email.
                         It could be a random glitch, or something could be permanently
                         broken. Please contact <a href="mailto:' . Configure::read('admin_email') . '">' . Configure::read('admin_email') . '</a> for assistance.');

                    return null;
                }
            }
        }
        $this->set([
             'titleForLayout' => 'Contact Us'
         ]);

        return null;
    }

    /**
     * view for the terms page
     *
     * @return void
     */
    public function terms()
    {
        $this->set('titleForLayout', 'Terms of Use and Privacy Policy');
    }
}
