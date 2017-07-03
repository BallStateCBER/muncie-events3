<?php
namespace App\Test\TestCase\Controller;

use App\Controller\TagsController;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestCase;

/**
 * App\Controller\TagsController Test Case
 */
class TagsControllerTest extends IntegrationTestCase
{
    /**
     * Test adding tags
     *
     * @return void
     */
    public function testAddingTags()
    {
        $this->Tags = TableRegistry::get('Tags');

        $this->session(['Auth.User.id' => 1]);

        $this->get('/tags/manage');
        $this->assertResponseOk();
        $this->assertResponseContains('Manage Tags');

        $newTag = [
            'name' => "Lourdes\n-Soothsayer Lies",
            'parent_name' => 'intelligent dance music'
        ];

        $this->post('/tags/add', $newTag);

        $this->assertResponseSuccess();

        $newTag = $this->Tags->find()
            ->where(['name' => 'lourdes'])
            ->andWhere(['parent_id' => 697])
            ->firstOrFail();

        $newChild = $this->Tags->find()
            ->where(['name' => 'soothsayer lies'])
            ->andWhere(['parent_id' => $newTag->id])
            ->firstOrFail();

        if ($newTag->name == 'lourdes' && $newChild->name == 'soothsayer lies') {
            return;
        }

        $this->assertResponseError();
    }

    /**
     * Test adding a tag that already exists
     *
     * @return void
     */
    public function testAddingExistingTag()
    {
        $this->Tags = TableRegistry::get('Tags');

        $this->session(['Auth.User.id' => 1]);

        $this->get('/tags/manage');
        $this->assertResponseOk();
        $this->assertResponseContains('Manage Tags');

        $newTag = [
            'name' => "Lourdes\n-Soothsayer Lies",
            'parent_name' => 'intelligent dance music'
        ];

        $this->post('/tags/add', $newTag);

        $tags = $this->Tags->find()
            ->where(['name' => 'lourdes'])
            ->orWhere(['name' => 'soothsayer lies'])
            ->count();

        if ($tags == 2) {
            $this->assertResponseSuccess();
            return;
        }

        $this->assertResponseError();
    }

    /**
     * Test editing tags
     *
     * @return void
     */
    public function testEditingTags()
    {
        $this->Tags = TableRegistry::get('Tags');
        $oldTag = $this->Tags->find()
            ->where(['name' => 'lourdes'])
            ->first();

        $this->session(['Auth.User.id' => 1]);

        $this->get("/tags/edit/lourdes");
        $this->assertResponseSuccess();

        $edits = [
            'name' => 'We the Heathens',
            'listed' => 1,
            'selectable' => 1,
            'parent_id' => 697,
            'id' => $oldTag->id
        ];

        $this->post('/tags/edit/lourdes', $edits);

        $this->assertResponseSuccess();
    }

    /**
     * Test merging tags
     *
     * @return void
     */
    public function testMergingTags()
    {
        $this->session(['Auth.User.id' => 1]);

        $this->Tags = TableRegistry::get('Tags');
        $this->EventsTags = TableRegistry::get('EventsTags');

        $oldTag = $this->Tags->find()
            ->where(['name' => 'soothsayer lies'])
            ->first();
        $newTag = $this->Tags->find()
            ->where(['name' => 'we the heathens'])
            ->first();

        $decoyJoin = $this->EventsTags->newEntity();
        $decoyJoin->event_id = 6;
        $decoyJoin->tag_id = $oldTag->id;
        if ($this->EventsTags->save($decoyJoin)) {
            $this->post("/tags/merge/$oldTag->name/$newTag->name");
        };

        $newJoin = $this->EventsTags->find()
            ->where(['event_id' => 6])
            ->andWhere(['tag_id' => $newTag->id])
            ->firstOrFail();

        if (isset($newJoin)) {
            $this->assertResponseSuccess();
            // because right now it's redirecting to /tags/manage which it's not the best
            $this->markTestIncomplete();
            $this->EventsTags->delete($newJoin);
        }
    }

    /**
     * Test deleting tags
     *
     * @return void
     */
    public function testDeletingTags()
    {
        $this->Tags = TableRegistry::get('Tags');

        $this->session(['Auth.User.id' => 1]);

        $this->get("/tags/remove/we%20the%20heathens");
        $this->assertResponseSuccess();

        $newTag = $this->Tags->find()
            ->where(['name' => 'we the heathens'])
            ->orWhere(['name' => 'soothsayer lies'])
            ->first();

        if (!isset($newTag->name)) {
            $this->assertResponseSuccess();
            return;
        }

        $this->assertResponseError();
    }
}
