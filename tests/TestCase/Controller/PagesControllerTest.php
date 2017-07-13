<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         1.2.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Test\TestCase\Controller;

use App\Controller\PagesController;
use Cake\Core\App;
use Cake\Core\Configure;
use Cake\Network\Request;
use Cake\Network\Response;
use Cake\TestSuite\IntegrationTestCase;
use Cake\View\Exception\MissingTemplateException;
use Facebook\FacebookSession;
use Facebook\FacebookRedirectLoginHelper;

/**
 * PagesControllerTest class
 */
class PagesControllerTest extends IntegrationTestCase
{
    /*
     * test that pages are loading.
     */
    public function testMultipleGet()
    {
        $this->get('/');
        $this->assertResponseOk();
        $this->get('/');
        $this->assertResponseOk();
    }

    public function testAboutPageLoads()
    {
        $this->get('/about');
        $this->assertResponseOk();
        $this->assertResponseContains('Erica Dee Fox');
        $this->assertResponseContains('</html>');
    }

    public function testContactPageLoads()
    {
        $this->get('/contact');
        $this->assertResponseOk();
        $this->assertResponseContains('site administrator');
        $this->assertResponseContains('</html>');
    }

    public function testContactPageSendsEmails()
    {
        $this->get('/contact');
        $this->assertResponseOk();

        $data = [
            'category' => 'General',
            'name' => 'Placeholder Man',
            'email' => 'ericadeefox@gmail.com',
            'body' => 'I am a placeholder'
        ];

        $this->post('/contact', $data);
        $this->assertResponseContains('Thanks for contacting us.');
        $this->assertResponseOk();
    }

    public function testTermsPageLoads()
    {
        $this->get('/terms');
        $this->assertResponseOk();
        $this->assertResponseContains('Revisions and Errata');
        $this->assertResponseContains('</html>');
    }
}
