<?php
namespace App\Test\TestCase\Controller;

use App\Controller\CategoriesController;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestCase;

/**
 * App\Controller\CategoriesController Test Case
 */
class EventsSeriesViewTest extends IntegrationTestCase
{
    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();
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
