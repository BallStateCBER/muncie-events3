<?php
// @codingStandardsIgnoreFile

use Migrations\AbstractMigration;

class RemoveTimeStartAndEnd extends AbstractMigration
{
    /**
     * migrate up
     *
     * @return void
     */
    public function up()
    {
        $table = $this->table('events');
        $table
            ->removeColumn('time_start')
            ->removeColumn('time_end')
            ->update();

        print_r(" All events have been updated!");
    }

    /**
     * migrate down
     *
     * @return void
     */
    public function down()
    {
        $table = $this->table('events');
        $table
            ->addColumn('time_start', 'time', ['after' => 'date', 'default' => '00:00:00'])
            ->addColumn('time_end', 'time', ['after' => 'time_start', 'default' => '00:00:00'])
            ->save();

        $stmt = $this->query("SELECT * FROM events");
        $events = $stmt->fetchAll();

        foreach ($events as $event) {
            $id = $event['id'];
            if (date('I', strtotime($event['start'])) == 1) {
                $dst = ' - 4 hours';
            }
            if (date('I', strtotime($event['start'])) == 0) {
                $dst = ' - 5 hours';
            }
            $start = date('H:i:s', strtotime($event['start'] . $dst));
            if (date('I', strtotime($event['end'])) == 1) {
                $dst = ' - 4 hours';
            }
            if (date('I', strtotime($event['end'])) == 0) {
                $dst = ' - 5 hours';
            }
            $end = date('H:i:s', strtotime($event['end'] . $dst));

            $this->execute("UPDATE events SET time_start='$start', time_end='$end' WHERE id='$id'");

            print_r("|");
        }
    }
}
