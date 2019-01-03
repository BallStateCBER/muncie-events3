<?php
use Migrations\AbstractMigration;

class MakeTimesNotDatetimes extends AbstractMigration
{
    /**
     * Up Method.
     *
     * @return void
     */
    public function up()
    {
        $this->table('events')
            ->changeColumn('start', 'time', ['default' => null])
            ->changeColumn('end', 'time', ['default' => null])
            ->save();
    }

    /**
     * Down Method.
     *
     * @return void
     */
    public function down()
    {
        $this->table('events')
            ->changeColumn('start', 'datetime', ['default' => null])
            ->changeColumn('end', 'datetime', ['default' => null, 'null' => true])
            ->save();
    }
}
