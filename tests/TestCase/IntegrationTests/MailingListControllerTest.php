<?php
namespace App\Test\TestCase\Controller;

use App\Test\TestCase\ApplicationTest;

class MailingListControllerTest extends ApplicationTest
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
     * test sendDaily method
     *
     * @return void
     */
    public function testSendDaily()
    {
        $this->session($this->admin);
        $this->get('/mailing-list/send-daily');
        $this->assertResponseOk();
    }

    /**
     * test sendWeekly method
     *
     * @return void
     */
    public function testSendWeekly()
    {
        $this->session($this->admin);
        $this->get('/mailing-list/send-weekly');
        $this->assertResponseOk();
    }

    /**
     * test join method
     *
     * @return void
     */
    public function testJoin()
    {
        $url = '/mailing-list/join';
        $this->get($url);
        $this->assertResponseOk();
        $formData = [
            'email' => 'placeholder@bsu.edu',
            'settings' => 'default',
            'weekly' => 0,
            'daily_sun' => 0,
            'daily_mon' => 0,
            'daily_tue' => 0,
            'daily_wed' => 0,
            'daily_thu' => 0,
            'daily_fri' => 0,
            'daily_sat' => 0,
            'event_categories' => 'all',
            'selected_categories' => [
                1 => 1,
                2 => 1
            ]
        ];
        $this->post($url, $formData);
        $mailingList = $this->MailingList->find()
            ->where(['email' => $formData['email']])
            ->firstOrFail();

        $this->assertTrue($mailingList['all_categories']);
        $this->assertTrue($mailingList['weekly']);
        $this->assertTrue($mailingList['new_subscriber']);

        $joins = $this->CategoriesMailingList->find()
            ->where(['mailing_list_id' => $mailingList['id']])
            ->count();

        $this->assertEquals(2, $joins);

        // let's test all the other ways people can sign up that basically amount to the same thing
        $formData['event_categories'] = 'custom';
        $formData['selected_categories'] = [
            1 => 1,
            2 => 1
        ];
        $this->post($url, $formData);
        $mailingList = $this->MailingList->find()
            ->where(['email' => $formData['email']])
            ->firstOrFail();
        $this->assertTrue($mailingList['all_categories']);

        $formData['event_categories'] = 'all';
        $formData['selected_categories'] = [
            1 => 0,
            2 => 0
        ];
        $this->post($url, $formData);
        $mailingList = $this->MailingList->find()
            ->where(['email' => $formData['email']])
            ->firstOrFail();
        $this->assertTrue($mailingList['all_categories']);
    }

    /**
     * test resetProcessedTime method
     *
     * @return void
     */
    public function testResetProcessedTime()
    {
        $this->session($this->admin);
        $this->get('/mailing-list/reset-processed-time');
        $this->assertResponseOk();
    }

    /**
     * test bulkAdd method
     *
     * @return void
     */
    public function testBulkAdd()
    {
        $this->session($this->admin);
        $this->get('/mailing-list/bulk-add');
        $this->assertResponseOk();
    }

    /**
     * test settings method
     *
     * @return void
     */
    public function testSettings()
    {
        $recipientId = $this->MailingList->find()
            ->where(['email' => 'ericadeefox@gmail.com'])
            ->firstOrFail();
        $hash = $this->MailingList->getHash($recipientId['id']);
        $url = "/mailing-list/settings/" . $recipientId['id'] . "/" . $hash;
        $this->get($url);
        $this->assertResponseOk();

        $formData = [
            'email' => 'americageepox@gmail.com',
            'frequency' => 'weekly',
            'weekly' => 0,
            'daily_sun' => 0,
            'daily_mon' => 0,
            'daily_tue' => 0,
            'daily_wed' => 0,
            'daily_thu' => 0,
            'daily_fri' => 0,
            'daily_sat' => 0,
            'event_categories' => 'custom',
            'selected_categories' => [
                1 => 1
            ],
            'unsubscribe' => 0
        ];
        $this->post($url, $formData);

        $this->MailingList->find()
            ->where(['id' => $recipientId['id']])
            ->andWhere(['weekly' => 1])
            ->firstOrFail();

        // making sure the original joinData was unset
        $this->CategoriesMailingList->find()
            ->where(['mailing_list_id' => $recipientId['id']])
            ->andWhere(['category_id' => 1])
            ->firstOrFail();

        $delJoin = $this->CategoriesMailingList->find()
            ->where(['mailing_list_id' => $recipientId['id']])
            ->andWhere(['category_id' => 2])
            ->first();
        $this->assertNull($delJoin);

        // actually what if you want to unsubscribe
        $formData['unsubscribe'] = 1;
        $this->post($url, $formData);

        $delUser = $this->MailingList->find()
            ->where(['id' => $recipientId['id']])
            ->first();
        $this->assertNull($delUser);

        $delJoin = $this->CategoriesMailingList->find()
            ->where(['mailing_list_id' => $recipientId['id']])
            ->first();
        $this->assertNull($delJoin);
    }
}
