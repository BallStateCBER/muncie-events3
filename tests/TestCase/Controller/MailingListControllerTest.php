<?php
namespace App\Test\TestCase\Controller;

use App\Controller\MailingListController;
use Cake\TestSuite\IntegrationTestCase;

/**
 * App\Controller\MailingListController Test Case
 */
class MailingListControllerTest extends IntegrationTestCase
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.mailing_list',
        'app.users',
        'app.mailing_lists',
        'app.facebooks',
        'app.event_series',
        'app.events',
        'app.categories',
        'app.categories_mailing_list',
        'app.series',
        'app.images',
        'app.events_images',
        'app.tags',
        'app.events_tags'
    ];

    /**
     * Test index method
     *
     * @return void
     */
    public function testIndex()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test view method
     *
     * @return void
     */
    public function testView()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test add method
     *
     * @return void
     */
    public function testAdd()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test edit method
     *
     * @return void
     */
    public function testEdit()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test delete method
     *
     * @return void
     */
    public function testDelete()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
