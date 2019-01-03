<?php
use Migrations\AbstractMigration;

class FixIncorrectDefaultDates extends AbstractMigration
{
    /**
     * Up Method.
     *
     * @return void
     */
    public function up()
    {
        $this->table('events')
            ->changeColumn('date', 'date', ['default' => null])
            ->changeColumn('start', 'datetime', ['default' => null])
            ->save();

        // Tables that need 'created' column updated
        $tableNames = [
            'mailing_list_log',
            'tags'
        ];
        foreach ($tableNames as $tableName) {
            $this->table($tableName)
                ->changeColumn('created', 'datetime', ['default' => null])
                ->save();
        }

        // Tables that need 'created' and 'modified' columns updated
        $tableNames = [
            'events',
            'events_images',
            'event_series',
            'images',
            'mailing_list',
            'users'
        ];
        foreach ($tableNames as $tableName) {
            $this->table($tableName)
                ->changeColumn('created', 'datetime', ['default' => null])
                ->changeColumn('modified', 'datetime', ['default' => null])
                ->save();
        }
    }

    /**
     * Down Method.
     *
     * @return void
     */
    public function down()
    {
        $table = $this->table('events');
        $table
            ->changeColumn('date', 'date', ['default' => '1969-12-31'])
            ->changeColumn('start', 'datetime', ['default' => '1969-12-31 00:00:00'])
            ->save();

        // Tables that need 'created' column updated
        $tableNames = [
            'mailing_list_log',
            'tags'
        ];
        foreach ($tableNames as $tableName) {
            $this->table($tableName)
                ->changeColumn('created', 'datetime', ['default' => '1969-12-31 23:59:59'])
                ->save();
        }

        // Tables that need 'created' and 'modified' columns updated
        $tableNames = [
            'events',
            'events_images',
            'event_series',
            'images',
            'mailing_list',
            'users'
        ];
        foreach ($tableNames as $tableName) {
            $this->table($tableName)
                ->changeColumn('created', 'datetime', ['default' => '1969-12-31 23:59:59'])
                ->changeColumn('modified', 'datetime', ['default' => '1969-12-31 23:59:59'])
                ->save();
        }
    }
}
