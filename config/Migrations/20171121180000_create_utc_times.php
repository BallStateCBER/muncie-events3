<?php

use Phinx\Migration\AbstractMigration;

class CreateUtcTimes extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     *
     * @return void
     */
    public function change()
    {
        $table = $this->table('events');
        $table->addColumn('start', 'string', ['limit' => 19])
            ->addColumn('end', 'string', ['limit' => 19, 'null' => true])
            ->save();

        $stmt = $this->query("SELECT * FROM events");
        $events = $stmt->fetchAll();

        foreach ($events as $event) {
            $id = $event['id'];
            $start = date('Y-m-d', strtotime($event['date'])) . 'T' . date('H:i:s', strtotime($event['time_start']));
            $end = isset($event['time_end']) ? date('Y-m-d', strtotime($event['date'])) . 'T' . date('H:i:s', strtotime($event['time_end'])) : null;

            $stmt = $this->query("UPDATE events SET start='$start', end='$end' WHERE id='$id'");

            print_r("Event $id has been updated");
        }
    }
}
