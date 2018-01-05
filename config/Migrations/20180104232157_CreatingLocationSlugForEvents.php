<?php
// @codingStandardsIgnoreFile

use Migrations\AbstractMigration;

class CreatingLocationSlugForEvents extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     * @return void
     */
    public function up()
    {
        $events = $this->table('events');
        $events
            ->addColumn('location_slug', 'string', ['after' => 'location_details', 'limit' => 20, 'null' => false])
            ->save();

        $stmt = $this->query('SELECT * FROM events');
        $events = $stmt->fetchAll();

        foreach ($events as $event) {
            $id = $event['id'];
            $locationSlug = strtolower($event['location']);
            $locationSlug = substr($locationSlug, 0, 20);
            $locationSlug = str_replace('/', ' ', $locationSlug);
            $locationSlug = preg_replace("/[^A-Za-z0-9 ]/", '', $locationSlug);
            $locationSlug = str_replace("   ", ' ', $locationSlug);
            $locationSlug = str_replace("  ", ' ', $locationSlug);
            $locationSlug = str_replace(' ', '-', $locationSlug);
            if (substr($locationSlug, -1) == '-') {
                $locationSlug = substr($locationSlug, 0, -1);
            }
            $this->execute('UPDATE events SET location_slug="' . $locationSlug . '" WHERE id="' . $id . '"');
        }
    }

    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     * @return void
     */
    public function down()
    {
        $events = $this->table('events');
        $events
            ->removeColumn('location_slug')
            ->save();
    }
}
