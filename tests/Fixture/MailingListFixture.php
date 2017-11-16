<?php
namespace App\Test\Fixture;

use FriendsOfCake\Fixturize\TestSuite\Fixture\ChecksumTestFixture as TestFixture;

/**
 * MailingListFixture
 *
 */
class MailingListFixture extends TestFixture
{
    /**
     * initialize fixture method
     */
    public function init()
    {
        parent::init();
        $this->records = [
            [
                'email' => 'adminplaceholder@bsu.edu',
                'all_categories' => 0,
                'weekly' => 1,
                'daily_sun' => 1,
                'daily_mon' => 1,
                'daily_tue' => 1,
                'daily_wed' => 1,
                'daily_thu' => 1,
                'daily_fri' => 1,
                'daily_sat' => 1,
                'new_subscriber' => 1,
                'created' => date('Y-m-d', strtotime('Today')),
                'modified' => date('Y-m-d', strtotime('Today'))
            ],
            [
                'email' => 'userplaceholder@bsu.edu',
                'all_categories' => 1,
                'weekly' => 0,
                'daily_sun' => 0,
                'daily_mon' => 1,
                'daily_tue' => 1,
                'daily_wed' => 1,
                'daily_thu' => 1,
                'daily_fri' => 1,
                'daily_sat' => 1,
                'new_subscriber' => 1,
                'created' => date('Y-m-d', strtotime('Today')),
                'modified' => date('Y-m-d', strtotime('Today'))
            ]
        ];
    }
    /**
     * Table name
     *
     * @var string
     */
    public $table = 'mailing_list';

    /**
     * Fields
     *
     * @var array
     */
    public $fields = [
        'id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'autoIncrement' => true, 'precision' => null],
        'email' => ['type' => 'string', 'length' => 200, 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'all_categories' => ['type' => 'boolean', 'length' => null, 'null' => false, 'default' => '1', 'comment' => '', 'precision' => null],
        'categories' => ['type' => 'string', 'length' => 50, 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'Not used, field was used in TheMuncieScene.com\'s mailing list and is temporarily retained to facilitate conversion to the new mailing list', 'precision' => null, 'fixed' => null],
        'weekly' => ['type' => 'boolean', 'length' => null, 'null' => false, 'default' => '0', 'comment' => '', 'precision' => null],
        'daily_sun' => ['type' => 'boolean', 'length' => null, 'null' => false, 'default' => '0', 'comment' => '', 'precision' => null],
        'daily_mon' => ['type' => 'boolean', 'length' => null, 'null' => false, 'default' => '0', 'comment' => '', 'precision' => null],
        'daily_tue' => ['type' => 'boolean', 'length' => null, 'null' => false, 'default' => '0', 'comment' => '', 'precision' => null],
        'daily_wed' => ['type' => 'boolean', 'length' => null, 'null' => false, 'default' => '0', 'comment' => '', 'precision' => null],
        'daily_thu' => ['type' => 'boolean', 'length' => null, 'null' => false, 'default' => '0', 'comment' => '', 'precision' => null],
        'daily_fri' => ['type' => 'boolean', 'length' => null, 'null' => false, 'default' => '0', 'comment' => '', 'precision' => null],
        'daily_sat' => ['type' => 'boolean', 'length' => null, 'null' => false, 'default' => '0', 'comment' => '', 'precision' => null],
        'new_subscriber' => ['type' => 'boolean', 'length' => null, 'null' => false, 'default' => '1', 'comment' => '', 'precision' => null],
        'created' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'modified' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'processed_daily' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'processed_weekly' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
        ],
        '_options' => [
            'engine' => 'MyISAM',
            'collation' => 'utf8_general_ci'
        ]
    ];
}
