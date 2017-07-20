<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ImagesTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ImagesTable Test Case
 */
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
        $config = TableRegistry::exists('Images') ? [] : ['className' => 'App\Model\Table\ImagesTable'];
        $this->Images = TableRegistry::get('Images', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Images);

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
        $this->assertEquals($nextId, $last);
    }
}
