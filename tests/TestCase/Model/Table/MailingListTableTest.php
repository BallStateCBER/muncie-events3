<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\MailingListTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\MailingListTable Test Case
 */
class MailingListTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Table\MailingListTable
     */
    public $MailingList;

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
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('MailingList') ? [] : ['className' => 'App\Model\Table\MailingListTable'];
        $this->MailingList = TableRegistry::get('MailingList', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->MailingList);

        parent::tearDown();
    }

    /**
     * Test initialize method
     *
     * @return void
     */
    public function testInitialize()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test validationDefault method
     *
     * @return void
     */
    public function testValidationDefault()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     */
    public function testBuildRules()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
