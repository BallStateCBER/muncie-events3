<?php
namespace App\Test\TestCase\Controller;

use App\Controller\CategoriesController;
use Cake\ORM\TableRegistry;
use App\Test\TestCase\AppControllerTest;

/**
 * App\Controller\CategoriesController Test Case
 */
class CategoriesViewTest extends AppControllerTest
{
    public function setUp()
    {
        parent::setUp();
    }

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
