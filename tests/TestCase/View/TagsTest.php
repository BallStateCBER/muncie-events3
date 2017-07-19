<?php
namespace App\Test\TestCase\Controller;

use App\Controller\CategoriesController;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestCase;
use Facebook\FacebookSession;
use Facebook\FacebookRedirectLoginHelper;

/**
 * App\Controller\CategoriesController Test Case
 */
class TagsViewTest extends IntegrationTestCase
{
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
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Tags);
        parent::tearDown();
    }

    /**
     * Test that ALL previously used tags are accessible
     *
     * @return void
     */
    public function testTagsIndex()
    {
        $this->get("tags/past");
        $this->assertResponseOk();

        $tags = $this->Tags->getAllWithCounts(['date <' => date('Y-m-d')]);

        foreach ($tags as $tag) {
            // irritatingly, we're replacing characters with their ascii codes
            $htmlTag = str_replace("&", "&amp;", $tag['name']);
            $htmlTag = str_replace("'", "&#039;", $htmlTag);
            $this->assertResponseContains($htmlTag);
        }
    }

    /**
     * Test tag admin page
     *
     * @return void
     */
    public function testTagAdminPrivileges()
    {
        $this->session(['Auth.User.id' => 1]);

        $this->get('/tags/getnodes');
        $this->assertResponseOk();
        $this->assertResponseContains('Delete (1011)');
        $this->assertResponseContains('Unlisted (1012)');
    }
}
