<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\TagsTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\TagsTable Test Case
 */
class TagsTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Table\TagsTable
     */
    public $Tags;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('Tags') ? [] : ['className' => 'App\Model\Table\TagsTable'];
        $this->Tags = TableRegistry::get('Tags', $config);
    #    $this->Events = TableRegistry::get('Events');
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Tags);
    #    unset($this->Events);

        parent::tearDown();
    }

    /**
     * Test getAllWithCounts method
     *
     * @return void
     */
    public function testGetAllWithCounts()
    {
        // looking for the tags associated with religion
        $conditions = [
            'category_id' => 27
        ];
        $counts = $this->Tags->getAllWithCounts($conditions);
        $counts = array_keys($counts);
        $counts = implode($counts);
        $this->assertContains('alcohol free', $counts);
        $this->assertContains('christianity', $counts);
    }

    /**
     * Test getCategoriesWithTags method
     *
     * @return void
     */
    public function testGetCategoriesWithTags()
    {
        $categories = $this->Tags->getCategoriesWithTags('future');
        debug($categories);
        $categories = $this->Tags->getCategoriesWithTags('past');
        debug($categories);
    }
}
