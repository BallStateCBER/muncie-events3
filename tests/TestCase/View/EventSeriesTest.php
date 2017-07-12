<?php
namespace App\Test\TestCase\Controller;

use App\Controller\CategoriesController;
use Cake\ORM\TableRegistry;
use App\Test\TestCase\AppControllerTest;

/**
 * App\Controller\CategoriesController Test Case
 */
class EventsSeriesViewTest extends AppControllerTest
{
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * Test that events series are viewable
     *
     * @return void
     */
    public function testSeriesView()
    {
        $this->get("/event-series/8");
        $this->assertResponseOk();
        $this->assertResponseContains('First Thursday Gallery Walk');
    }
}
