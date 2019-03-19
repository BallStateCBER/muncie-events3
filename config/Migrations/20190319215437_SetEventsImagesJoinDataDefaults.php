<?php
use Migrations\AbstractMigration;

class SetEventsImagesJoinDataDefaults extends AbstractMigration
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
        $table = $this->table('events_images');
        $table->changeColumn('weight', 'integer', [
            'default' => 0
        ]);
        $table->changeColumn('caption', 'string', [
            'default' => ''
        ]);
    }
}
