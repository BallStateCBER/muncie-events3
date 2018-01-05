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
        // clean up errors in inputting locations
        $this->execute('UPDATE events SET location_details="Music Room", location="Cornerstone Center for the Arts" WHERE id="5"');
        $this->execute('UPDATE events SET location="The Mark III Tap Room", address="306 S. Walnut St." WHERE id="1130"');
        $this->execute('UPDATE events SET location="Northside Church of the Nazarene", address="3801 N. Wheeling Ave." WHERE id="4691"');
        $this->execute('UPDATE events SET location="Be Here Now", address="505 N. Dill St." WHERE id="2853"');
        $this->execute('UPDATE events SET location="Cornerstone Center for the Arts", address="520 E. Main St." WHERE id="4561"');
        $this->execute('UPDATE events SET location="Lotus Yoga Studio", address="814 W. White River Blvd." WHERE id="4116"');
        $this->execute('UPDATE events SET location="AMC Showplace Muncie 12" WHERE id="3421"');

        // oh wow, here's all the different ways ppl have learned to enter BSU buildings over the years
        $this->execute('UPDATE events SET location="Art and Journalism Building", location_details="Room 101", address="1001 N. McKinley Ave." WHERE id="1011"');
        $this->execute('UPDATE events SET location="Art and Journalism Building", location_details="Room 125", address="1001 N. McKinley Ave." WHERE id="962"');
        $this->execute('UPDATE events SET location="Art and Journalism Building", location_details="Room 175", address="1001 N. McKinley Ave." WHERE location="AJ 175" OR location="Art and Journalism Building Room 175" OR location="Art and Journalism Building room 175" OR location="The Art and Journalism Building, room 175" OR location="Ball State University AJ175" OR location="Ball State University, AJ 175" OR id="1167" OR id="2294" OR id="3355"');
        $this->execute('UPDATE events SET location="Art and Journalism Building", location_details="Room 225", address="1001 N. McKinley Ave." WHERE location="Art and Journalism Building Room 225" OR location="Ball State University, AJ 225" OR id="301"');
        $this->execute('UPDATE events SET location="Art and Journalism Building", location_details="Room 289", address="1001 N. McKinley Ave." WHERE id="1219"');
        $this->execute('UPDATE events SET location="Art and Journalism Building", location_details="Atrium Gallery", address="1001 N. McKinley Ave." WHERE id="3635"');

        $this->execute('UPDATE events SET location="Architecture Building", location_details="Room 100" WHERE id="183" OR id="1562" OR id="1289"');

        $this->execute('UPDATE events SET location="David Owsley Museum of Art", location_details="Room 217" WHERE location="Art Museum Room 217, Ball State University"');
        $this->execute('UPDATE events SET location="David Owsley Museum of Art" WHERE id="129" OR id="158" OR location="Ball State Museum of Art" OR location="Ball State University Museum of Art" OR location="David Owsley Museum of Art, Ball State University" OR location="David Owsley Museum of Art, Fine Arts Building"');
        $this->execute('UPDATE events SET location="David Owsley Museum of Art", location_details="Recital Hall" WHERE id="1490"');

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
