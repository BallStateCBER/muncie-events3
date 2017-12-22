<?php
// @codingStandardsIgnoreFile

use Migrations\AbstractMigration;

class SetCharacterToUtf8mb4 extends AbstractMigration
{
    /**
     * Up Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     * @return void
     */
    public function up()
    {
        $this->execute('ALTER DATABASE okbvtfr_muncieevents CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;');

        $tables = [
            'categories',
            'categories_mailing_list',
            'events',
            'event_series',
            'events_images',
            'events_tags',
            'images',
            'mailing_list',
            'mailing_list_log',
            'tags',
            'users'
        ];
        foreach ($tables as $table) {
            $this->execute("ALTER TABLE $table CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
        }

        $table = $this->table('events');
        $table->changeColumn('description', 'text', [
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci'
        ]);

        $table = $this->table('users');
        $table->changeColumn('bio', 'text', [
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci'
        ]);
    }

    /**
     * Down Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     * @return void
     */
    public function down()
    {
        $this->execute('ALTER DATABASE okbvtfr_muncieevents CHARACTER SET = utf8 COLLATE = utf8_unicode_ci;');

        $tables = [
            'categories',
            'categories_mailing_list',
            'event_series',
            'events_images',
            'events_tags',
            'images',
            'mailing_list',
            'mailing_list_log',
            'tags'
        ];
        foreach ($tables as $table) {
            $this->table($table, ['collation' => 'utf8']);
        }

        $table = $this->table('events', ['collation' => 'utf8_unicode_ci']);
        $table->changeColumn('description', 'text', [
            'encoding' => 'utf8',
            'collation' => 'utf8_unicode_ci'
        ]);

        $table = $this->table('users', ['collation' => 'utf8_unicode_ci']);
        $table->changeColumn('bio', 'text', [
            'encoding' => 'utf8',
            'collation' => 'utf8_unicode_ci'
        ]);
    }
}
