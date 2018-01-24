<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * EventsFixture
 *
 */
class EventsFixture extends TestFixture
{
    /**
     * initialize fixture method
     */
    public function init()
    {
        parent::init();
        $this->records = [
            [
                'title' => 'Placeholder Event Series',
                'description' => 'Lots of events in this placeholder series. Come on out!',
                'location' => 'Be Here Now',
                'location_slug' => 'be-here-now',
                'address' => '505 N. Dill St.',
                'user_id' => 1,
                'category_id' => 2,
                'series_id' => 1,
                'date' => date('Y-m-d'),
                'published' => 0,
                'approved_by' => null,
                'created' => date('Y-m-d', strtotime('Today')),
                'modified' => date('Y-m-d', strtotime('Today')),
                'start' => date('Y-m-d H:i:s', strtotime('Today 23:59:59')),
                'end' => date('Y-m-d H:i:s', strtotime('Today 23:59:59'))
            ],
            [
                'title' => 'Placeholder Event Series',
                'description' => 'Lots of events in this placeholder series. Come on out!',
                'location' => 'Be Here Now',
                'location_slug' => 'be-here-now',
                'address' => '505 N. Dill St.',
                'user_id' => 1,
                'category_id' => 2,
                'series_id' => 1,
                'date' => date('Y-m-d', strtotime('+1 day')),
                'published' => 0,
                'approved_by' => null,
                'created' => date('Y-m-d', strtotime('Today')),
                'modified' => date('Y-m-d', strtotime('Today')),
                'start' => date('Y-m-d H:i:s', strtotime('+1 day 23:59:59')),
                'end' => date('Y-m-d H:i:s', strtotime('+1 day 23:59:59'))
            ],
            [
                'title' => 'Placeholder Event Series',
                'description' => 'Lots of events in this placeholder series. Come on out!',
                'location' => 'Be Here Now',
                'location_slug' => 'be-here-now',
                'address' => '505 N. Dill St.',
                'user_id' => 1,
                'category_id' => 2,
                'series_id' => 1,
                'date' => date('Y-m-d', strtotime('+1 week')),
                'published' => 0,
                'approved_by' => null,
                'created' => date('Y-m-d', strtotime('Today')),
                'modified' => date('Y-m-d', strtotime('Today')),
                'start' => date('Y-m-d H:i:s', strtotime('+1 week 23:59:59')),
                'end' => date('Y-m-d H:i:s', strtotime('+1 week 23:59:59'))
            ],
            [
                'title' => 'Placeholder Event Regular',
                'description' => 'Just one event for this bad boy!!!!',
                'location' => 'Placeholder Place',
                'location_slug' => 'placeholder-place',
                'address' => '505 N. Bill St.',
                'user_id' => 1,
                'category_id' => 2,
                'series_id' => 1,
                'date' => date('Y-m-d', strtotime('Today')),
                'published' => 1,
                'approved_by' => 1,
                'created' => date('Y-m-d', strtotime('Today')),
                'modified' => date('Y-m-d', strtotime('Today')),
                'start' => date('Y-m-d H:i:s', strtotime('Today 23:59:59')),
                'end' => date('Y-m-d H:i:s', strtotime('Today 23:59:59'))
            ],
            [
                'title' => 'Placeholder Event From Long Ago',
                'description' => 'Just one event for this bad boy!!!!',
                'location' => 'Placeholder Place',
                'location_slug' => 'placeholder-place',
                'address' => '505 N. Bill St.',
                'user_id' => 1,
                'category_id' => 2,
                'series_id' => 1,
                'date' => date('Y-m-d', strtotime('-2 weeks')),
                'published' => 1,
                'approved_by' => 1,
                'created' => date('Y-m-d', strtotime('Today')),
                'modified' => date('Y-m-d', strtotime('Today')),
                'start' => date('Y-m-d H:i:s', strtotime('-2 weeks 23:59:59')),
                'end' => date('Y-m-d H:i:s', strtotime('-2 weeks 23:59:59'))
            ]
        ];
    }
    /**
     * Fields
     *
     * @var array
     */
    public $fields = [
        'id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'autoIncrement' => true, 'precision' => null],
        'title' => ['type' => 'string', 'length' => 100, 'null' => false, 'default' => '', 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'description' => ['type' => 'text', 'length' => null, 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null],
        'location' => ['type' => 'string', 'length' => 50, 'null' => false, 'default' => '', 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'location_details' => ['type' => 'string', 'length' => 100, 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'location_slug' => ['type' => 'string', 'length' => 20, 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'address' => ['type' => 'string', 'length' => 100, 'null' => false, 'default' => '', 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'user_id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => true, 'default' => '0', 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'category_id' => ['type' => 'integer', 'length' => 6, 'unsigned' => false, 'null' => false, 'default' => '0', 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'series_id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'date' => ['type' => 'date', 'length' => null, 'null' => false, 'default' => '1969-12-31', 'comment' => '', 'precision' => null],
        'age_restriction' => ['type' => 'string', 'length' => 30, 'null' => false, 'default' => '', 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'cost' => ['type' => 'string', 'length' => 200, 'null' => false, 'default' => '', 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'source' => ['type' => 'string', 'length' => 200, 'null' => false, 'default' => '', 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'published' => ['type' => 'boolean', 'length' => null, 'null' => false, 'default' => '0', 'comment' => '', 'precision' => null],
        'created' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'approved_by' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'modified' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'start' => ['type' => 'datetime', 'length' => null, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null],
        'end' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        '_indexes' => [
            'person_id' => ['type' => 'index', 'columns' => ['user_id'], 'length' => []],
            'category_id' => ['type' => 'index', 'columns' => ['category_id'], 'length' => []],
        ],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
        ],
        '_options' => [
            'engine' => 'MyISAM',
            'collation' => 'latin1_general_ci'
        ],
    ];
}
