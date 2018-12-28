<?php
namespace App\Test\TestCase\Controller;

use Cake\TestSuite\TestCase;

class CategoriesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\CategoriesTable
     */
    public $Categories;

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

    /**
     * Test getCategoriesWithEvents method
     *
     * @return void
     */
    public function testGetCategoriesWithEvents()
    {
        $categories = $this->Categories->getCategoriesWithEvents('past');
        $categories = implode(',', $categories);
        $this->assertContains('2', $categories);
        $categories = $this->Categories->getCategoriesWithEvents('future');
        $categories = implode(',', $categories);
        $this->assertContains('2', $categories);
    }
}
