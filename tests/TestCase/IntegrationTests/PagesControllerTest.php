<?php
namespace App\Test\TestCase\Controller;

use App\Test\TestCase\ApplicationTest;

class PagesControllerTest extends ApplicationTest
{
    /*
     * test that pages are loading.
     *
     * @return void
     */
    public function testMultipleGet()
    {
        $this->get('/');
        $this->assertResponseOk();
        $this->get('/');
        $this->assertResponseOk();
    }

    /*
     * test that the about page loads
     *
     * @return void
     */
    public function testAboutPageLoads()
    {
        $this->get('/about');
        $this->assertResponseOk();
        $this->assertResponseContains('Erica Dee Fox');
        $this->assertResponseContains('</html>');
    }

    /*
     * test that the contact page loads
     *
     * @return void
     */
    public function testContactPageLoads()
    {
        $this->get('/contact');
        $this->assertResponseOk();
        $this->assertResponseContains('site administrator');
        $this->assertResponseContains('</html>');
    }

    /*
     * test that the contact page sends emails
     *
     * @return void
     */
    public function testContactPageSendsEmails()
    {
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

    /*
     * test that our ToS loads
     *
     * @return void
     */
    public function testTermsPageLoads()
    {
        $this->get('/terms');
        $this->assertResponseOk();
        $this->assertResponseContains('Revisions and Errata');
        $this->assertResponseContains('</html>');
    }
}
