<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * UsersFixture
 *
 */
class UsersFixture extends TestFixture
{
    /**
     * initialize fixture method
     */
    public function init()
    {
        parent::init();

        // password is "placeholder"
        $this->records = [
            [
                'name' => 'Ash Admin',
                'role' => 'admin',
                'bio' => 'I am the admin and I do admin things.',
                'email' => 'adminplaceholder@bsu.edu',
                'password' => '$2y$10$89BFsNtHA/AcAfjom896Ouhw5KFtPsll5Oox0LWjilYCCxizqg.Jy',
                'mailing_list_id' => 1,
                'created' => date('Y-m-d', strtotime('Today')),
                'modified' => date('Y-m-d', strtotime('Today'))
            ],
            [
                'name' => 'Stevie User',
                'role' => 'user',
                'bio' => 'I sit around listening to witch house and not doing anything.',
                'email' => 'userplaceholder@bsu.edu',
                'password' => '$2y$10$MpeYbiU6QU0CYnEgzX.igO1z8V8oOfMqe6tzjP7kvrVQ5H8dnWpsm',
                'mailing_list_id' => 2,
                'created' => date('Y-m-d', strtotime('Today')),
                'modified' => date('Y-m-d', strtotime('Today'))
            ],
            [
                'name' => 'Paulie Placeholder',
                'role' => 'user',
                'bio' => 'I am yet another placeholder.',
                'email' => 'userplaceholder2@bsu.edu',
                'password' => '$2y$10$5UBkDk5/XUBtvdYm5iM6Vu7lje0Uv9LqBXRf8BMXnk/qUsVVTFSnW',
                'created' => date('Y-m-d', strtotime('Today')),
                'modified' => date('Y-m-d', strtotime('Today'))
            ]
        ];
    }
    /**
     * Fields
     *
     * @var array
     */
    // @codingStandardsIgnoreStart
    public $fields = [
        'id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'autoIncrement' => true, 'precision' => null],
        'name' => ['type' => 'string', 'length' => 100, 'null' => false, 'default' => '', 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'role' => ['type' => 'string', 'length' => 20, 'null' => false, 'default' => 'user', 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'bio' => ['type' => 'text', 'length' => null, 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null],
        'email' => ['type' => 'string', 'length' => 100, 'null' => false, 'default' => '', 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'password' => ['type' => 'string', 'length' => 64, 'null' => false, 'default' => '', 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'mailing_list_id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'facebook_id' => ['type' => 'biginteger', 'length' => 20, 'unsigned' => true, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'created' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'modified' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
        ],
        '_options' => [
            'engine' => 'MyISAM',
            'collation' => 'utf8_general_ci'
        ]
    ];
}
