<?php
namespace App\Test\TestCase\Controller;

use App\Controller\PagesController;
use App\Test\TestCase\ApplicationTest;
use Cake\Core\App;
use Cake\Core\Configure;
use Cake\Network\Request;
use Cake\Network\Response;
use Cake\View\Exception\MissingTemplateException;

/**
 * PagesControllerTest class
 */
class PagesControllerTest extends ApplicationTest
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
