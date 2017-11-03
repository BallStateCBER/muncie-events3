<?php
namespace App\Test\TestCase\Controller;

use App\Test\TestCase\ApplicationTest;

class CategoriesTableTest extends ApplicationTest
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
     * Test getName method
     *
     * @return void
     */
    public function testGetName()
    {
        $result = $this->Categories->getName(1);
        $this->assertEquals('General Events', $result);
    }
}
