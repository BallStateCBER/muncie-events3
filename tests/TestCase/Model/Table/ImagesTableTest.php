<?php
namespace App\Test\TestCase\Model\Table;

use Cake\TestSuite\TestCase;

class ImagesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ImagesTable
     */
    public $Images;

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
     * Test getNextId method
     *
     * @return void
     */
    public function testGetNextId()
    {
        $nextId = $this->Images->getNextId();
        $image = $this->Images->find('list');
        foreach ($image as $id => $filename) {
            $id = intval($id);
            $last = $id + 1;
        }
        if (!isset($last)) {
            $last = null;
        }
        $this->assertEquals($nextId, $last);
    }
}
