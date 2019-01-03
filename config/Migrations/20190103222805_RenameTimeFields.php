<?php
use Migrations\AbstractMigration;

class RenameTimeFields extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     * @return void
     */
    public function change()
    {
        $this->table('events')
            ->renameColumn('start', 'time_start')
            ->renameColumn('end', 'time_end')
            ->save();
    }
}
