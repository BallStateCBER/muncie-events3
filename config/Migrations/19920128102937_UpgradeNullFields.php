<?php
// @codingStandardsIgnoreFile

/*use Migrations\AbstractMigration;

class UpgradeNullFields extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     * @return void
     */
    /* public function change()
    {
        $table = $this->table('events');
        $table
            ->changeColumn('date', 'date', ['default' => '1969-12-31'])
            ->changeColumn('time_start', 'time', ['default' => '23:59:59'])
            ->changeColumn('time_end', 'time', ['default' => '23:59:59'])
            ->changeColumn('created', 'datetime', ['default' => '1969-12-31 23:59:59'])
            ->changeColumn('modified', 'datetime', ['default' => '1969-12-31 23:59:59'])
            ->save();

        $table = $this->table('event_series');
        $table
            ->changeColumn('created', 'datetime', ['default' => '1969-12-31 23:59:59'])
            ->changeColumn('modified', 'datetime', ['default' => '1969-12-31 23:59:59'])
            ->save();

        $table = $this->table('events_images');
        $table
            ->changeColumn('created', 'datetime', ['default' => '1969-12-31 23:59:59'])
            ->changeColumn('modified', 'datetime', ['default' => '1969-12-31 23:59:59'])
            ->save();

        $table = $this->table('images');
        $table
            ->changeColumn('created', 'datetime', ['default' => '1969-12-31 23:59:59'])
            ->changeColumn('modified', 'datetime', ['default' => '1969-12-31 23:59:59'])
            ->save();

        $table = $this->table('mailing_list');
        $table
            ->changeColumn('created', 'datetime', ['default' => '1969-12-31 23:59:59'])
            ->changeColumn('modified', 'datetime', ['default' => '1969-12-31 23:59:59'])
            ->save();

        $table = $this->table('mailing_list_log');
        $table
            ->changeColumn('created', 'datetime', ['default' => '1969-12-31 23:59:59'])
            ->save();

        $table = $this->table('tags');
        $table
            ->changeColumn('created', 'datetime', ['default' => '1969-12-31 23:59:59'])
            ->save();

        $table = $this->table('users');
        $table
            ->changeColumn('created', 'datetime', ['default' => '1969-12-31 23:59:59'])
            ->changeColumn('modified', 'datetime', ['default' => '1969-12-31 23:59:59'])
            ->save();
    }
}
*/