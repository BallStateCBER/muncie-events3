<?php
namespace App\Test\TestCase\Controller;

use App\Test\TestCase\ApplicationTest;

class TagsTableTest extends ApplicationTest
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
     * Test getDeleteGroupId method
     *
     * @return void
     */
    public function testGetDeleteGroupId()
    {
        $del = $this->Tags->getDeleteGroupId();
        $this->assertEquals(1011, $del);
    }

    /**
     * Test getIdFromName method
     *
     * @return void
     */
    public function testGetIdFromName()
    {
        $tag = $this->Tags->getIdFromName('delete');
        $this->assertEquals(1011, $tag);
        $tag = $this->Tags->getIdFromName('unlisted');
        $this->assertEquals(1012, $tag);
    }

    /**
     * Test getIdFromSlug method
     *
     * @return void
     */
    public function testGetIdFromSlug()
    {
        $tag = $this->Tags->getIdFromSlug('1011_delete');
        $this->assertEquals(1011, $tag);
        $tag = $this->Tags->getIdFromSlug('1012_unlisted');
        $this->assertEquals(1012, $tag);
    }

    /**
     * Test getIndentLevel method
     *
     * @return void
     */
    public function testGetIndentLevel()
    {
        $name = '--Yelawolf';
        $level = $this->Tags->getIndentLevel($name);
        $this->assertEquals(2, $level);
        $name = 'Best Friend';
        $level = $this->Tags->getIndentLevel($name);
        $this->assertEquals(0, $level);
    }

    /**
     * Test getTagFromId method
     *
     * @return void
     */
    public function testGetTagFromId()
    {
        $tag = $this->Tags->getTagFromId(1011);
        $this->assertEquals('delete', $tag->name);
        $tag = $this->Tags->getTagFromId(9214878513758);
        $this->assertEquals(null, $tag);
    }

    /**
     * Test getUnlistedGroupId method
     *
     * @return void
     */
    public function testGetUnlistedGroupId()
    {
        $unl = $this->Tags->getUnlistedGroupId();
        $this->assertEquals(1012, $unl);
    }

    /**
     * Test getUpcoming method
     *
     * @return void
     */
    public function testGetUpcoming()
    {
        $counts = $this->Tags->getUpcoming(['direction' => 'future']);
        $counts = array_keys($counts);
        $counts = implode($counts);
        $this->assertEquals($counts, null);
    }

    /**
     * Test getUsedTagIds method
     *
     * @return void
     */
    public function testGetUsedTagIds()
    {
        $used = $this->Tags->getUsedTagIds();
        $used = implode(',', $used);
        $this->assertContains('1', $used);
    }

    /**
     * Test getWithCounts method
     *
     * @return void
     */
    public function testGetWithCounts()
    {
        $counts = $this->Tags->getWithCounts([
            'direction' => 'future'
        ], 'alpha');
        $counts = array_keys($counts);
        $counts = implode($counts);
        $this->assertEquals($counts, null);
    }

    /**
     * Test isUnderUnlistedGroup method
     *
     * @return void
     */
    public function testIsUnderUnlistedGroup()
    {
        $newTag = $this->Tags->newEntity();
        $newTag->parent_id = $this->Tags->getUnlistedGroupId();
        $newTag->name = 'tester';
        $newTag->listed = 0;
        $newTag->selectable = 0;
        $this->Tags->save($newTag);
        $unl = $this->Tags->isUnderUnlistedGroup($newTag->id);
        $this->assertEquals(true, $unl);
    }
}
