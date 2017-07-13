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
class CategoriesViewTest extends IntegrationTestCase
{
    /**
     * Test category indexes
     *
     * @return void
     */
    public function testCategoryViews()
    {
        $this->Categories = TableRegistry::get('Categories');
        $categories = $this->Categories->getAll();

        foreach ($categories as $category) {
            $this->get("/$category->slug");
            $this->assertResponseOk();
            $this->assertResponseContains('event_accordion');
        }
    }
}
