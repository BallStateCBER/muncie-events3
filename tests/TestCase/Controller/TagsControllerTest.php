<?php
namespace App\Test\TestCase\Controller;

use App\Test\TestCase\ApplicationTest;

class TagsControllerTest extends ApplicationTest
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
     * Test adding, editing, and deleting tags
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testTagLifecycle()
    {
        $this->session($this->admin);
        $this->session(['Auth.User.id' => 1]);
        $this->get('/tags/manage');
        $this->assertResponseOk();
        $this->assertResponseContains('Manage Tags');
        $newTag = [
            'name' => "Lourdes\n-Soothsayer Lies",
            'parent_name' => 'holding places'
        ];
        $this->post('/tags/add', $newTag);
        $this->assertResponseSuccess();
        $newTag = $this->Tags->find()
            ->where(['name' => 'lourdes'])
            ->andWhere(['parent_id' => 1])
            ->firstOrFail();
        $newChild = $this->Tags->find()
            ->where(['name' => 'soothsayer lies'])
            ->andWhere(['parent_id' => $newTag->id])
            ->firstOrFail();
        if ($newTag['name'] == 'lourdes' && $newChild['name'] == 'soothsayer lies') {
            return;
        }
        $this->assertResponseError();

        /**
         * Test adding a tag that already exists
         */

        $this->get('/tags/manage');
        $this->assertResponseOk();
        $this->assertResponseContains('Manage Tags');
        $newTag = [
            'name' => "Lourdes\n-Soothsayer Lies",
            'parent_name' => 'holding places'
        ];
        $this->post('/tags/add', $newTag);
        $tags = $this->Tags->find()
            ->where([
                'OR' => [
                    ['name' => 'lourdes'],
                    ['name' => 'soothsayer lies']
                ]

            ])
            ->count();
        if ($tags == 2) {
            $this->assertResponseSuccess();

            return;
        }
        $this->assertResponseError();

        /**
         * Test editing tags
         */

        $oldTag = $this->Tags->find()
            ->where(['name' => 'lourdes'])
            ->first();
        $this->get("/tags/edit/lourdes");
        $this->assertResponseSuccess();
        $edits = [
            'name' => 'We the Heathens',
            'listed' => 1,
            'selectable' => 1,
            'parent_id' => 1,
            'id' => $oldTag->id
        ];
        $this->post('/tags/edit/lourdes', $edits);
        $this->assertResponseSuccess();

        /**
         * Test merging tags
         */

        $oldTag = $this->Tags->find()
            ->where(['name' => 'soothsayer lies'])
            ->first();
        $newTag = $this->Tags->find()
            ->where(['name' => 'we the heathens'])
            ->first();
        $decoyJoin = $this->EventsTags->newEntity();
        $decoyJoin->event_id = 1;
        $decoyJoin->tag_id = $oldTag->id;
        if ($this->EventsTags->save($decoyJoin)) {
            $oldName = $oldTag['name'];
            $newName = $newTag['name'];
            $this->post("/tags/merge/$oldName/$newName");
        };
        $newJoin = $this->EventsTags->find()
            ->where(['event_id' => 1])
            ->andWhere(['tag_id' => $newTag->id])
            ->firstOrFail();
        if (isset($newJoin)) {
            $this->assertResponseSuccess();
            $this->EventsTags->delete($newJoin);
        }

        /**
         * Test deleting tags
         */

        $this->get("/tags/remove/we%20the%20heathens");
        $this->assertResponseSuccess();
        $newTag = $this->Tags->find()
            ->where([
                'OR' => [
                    ['name' => 'we the heathens'],
                    ['name' => 'soothsayer lies']
                ]
            ])
            ->first();
        if (!isset($newTag->name)) {
            $this->assertResponseSuccess();
            $this->get("/tags/remove/nobody%20loves%20me");
            $this->assertResponseSuccess();

            return;
        }
        $this->assertResponseError();
    }

    /**
     * Test tag fixing functions
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testRegroupingOrphanTags()
    {
        $this->session($this->admin);
        for ($x = 0; $x <= 10; $x++) {
            $orphanTag = $this->Tags->newEntity([
                'name' => 'nobody loves me',
                'listed' => 0,
                'selectable' => 0,
                'user_id' => 1
            ]);
            $this->Tags->save($orphanTag);
        }
        $this->get('/tags/group-unlisted');
        $this->assertResponseOk();
        $adoptedTag = $this->Tags->find()
            ->where(['name' => 'nobody loves me'])
            ->andWhere(['parent_id' => 1012])
            ->toArray();
        if ($adoptedTag) {
            $this->assertResponseSuccess();
            foreach ($adoptedTag as $tag) {
                $tag->parent_id = null;
                $this->Tags->save($tag);
            }

            return;
        }
        $this->assertResponseError();

        /**
         * Test recovering tag tree structure
         */

        $this->get('/tags/recover');
        $this->assertResponseOk();

        /**
         * Test the removeUnlistedUnused() action
         */

        $this->get('/tags/remove-unlisted-unused');
        $this->assertResponseOk();
        $deadTag = $this->Tags->find()
            ->where(['name' => 'Nobody loves me'])
            ->first();
        $this->assertResponseOk();
        if (isset($deadTag)) {
            $this->assertResponseError();
        }

        /**
         * Test merging duplicates
         */

        for ($x = 0; $x <= 10; $x++) {
            $duplicate = $this->Tags->newEntity([
                'name' => 'nobody loves me',
                'listed' => 0,
                'selectable' => 0,
                'user_id' => 1
            ]);
            $this->Tags->save($duplicate);
        }
        $this->get('/tags/duplicates');
        $this->assertResponseOk();
        $duplicates = $this->Tags->find()
            ->where(['name' => 'nobody loves me'])
            ->count();
        if ($duplicates != 1) {
            $this->assertResponseError();
        }

        /**
         * Test removing broken associations
         */

        $broken = $this->EventsTags->newEntity();
        $broken->event_id = 99999;
        $broken->tag_id = 99999;
        $this->EventsTags->save($broken);
        $this->get('/tags/remove-broken-associations');
        $this->assertResponseOk();
        $this->assertResponseContains('Removed associations');
        $broken = $this->EventsTags->find()
            ->where(['event_id' => 99999])
            ->count();
        if ($broken > 0) {
            $this->assertResponseError();
        }
    }
}
