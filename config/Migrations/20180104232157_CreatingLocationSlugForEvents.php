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
        $this->execute('UPDATE events SET location="Ball Memorial Hospital", location_details="Auditorium" WHERE id="30"');

        // oh wow, here's all the different ways ppl have learned to enter BSU buildings over the years
        $this->execute('UPDATE events SET location="Art and Journalism Building, Ball State University", location_details="Room 101", address="1001 N. McKinley Ave." WHERE id="1011"');
        $this->execute('UPDATE events SET location="Art and Journalism Building, Ball State University", location_details="Room 125", address="1001 N. McKinley Ave." WHERE id="962"');
        $this->execute('UPDATE events SET location="Art and Journalism Building, Ball State University", location_details="Room 175", address="1001 N. McKinley Ave." WHERE location="AJ 175" OR location="Art and Journalism Building, Ball State University Room 175" OR location="Art and Journalism Building, Ball State University room 175" OR location="The Art and Journalism Building, Ball State University, room 175" OR location="Ball State University AJ175" OR location="Ball State University, AJ 175" OR id="1167" OR id="2294" OR id="3355"');
        $this->execute('UPDATE events SET location="Art and Journalism Building, Ball State University", location_details="Room 225", address="1001 N. McKinley Ave." WHERE location="Art and Journalism Building, Ball State University Room 225" OR location="Ball State University, AJ 225" OR id="301" OR id="3594" OR id="3930"');
        $this->execute('UPDATE events SET location="Art and Journalism Building, Ball State University", location_details="Room 289", address="1001 N. McKinley Ave." WHERE id="1219"');
        $this->execute('UPDATE events SET location="Art and Journalism Building, Ball State University", location_details="Atrium", address="1001 N. McKinley Ave." WHERE location="Ball State University Atrium"');
        $this->execute('UPDATE events SET location="Art and Journalism Building, Ball State University", location_details="Atrium Gallery", address="1001 N. McKinley Ave." WHERE id="3635"');
        $this->execute('UPDATE events SET location="Art and Journalism Building, Ball State University", location_details="Atrium Patio", address="1001 N. McKinley Ave." WHERE id="1292"');

        $this->execute('UPDATE events SET location="Architecture Building, Ball State University", location_details="Room 100" WHERE id="183" OR id="1562" OR id="1289"');

        $this->execute('UPDATE events SET location="Museum of Art, Ball State University", location_details="Room 217" WHERE location="Art Museum Room 217, Ball State University"');
        $this->execute('UPDATE events SET location="Museum of Art, Ball State University" WHERE id="129" OR id="158" OR location="Ball State Museum of Art" OR location="Ball State University Museum of Art" OR location="Museum of Art, Ball State University, Ball State University" OR location="Museum of Art, Ball State University, Fine Arts Building"');
        $this->execute('UPDATE events SET location="Museum of Art, Ball State University", location_details="Recital Hall" WHERE id="1490"');

        $this->execute('UPDATE events SET location="Arts Terrace, Ball State University", address="2021 Riverside Ave." WHERE location="BSU Arts Terrace" OR location="Ball State University Arts Terrace"');

        $this->execute('UPDATE events SET location="Bracken Library, Ball State University", location_details="Room 104" WHERE id="94" OR id="259" OR id="281" OR id="1714" or id="335"');
        $this->execute('UPDATE events SET location="Bracken Library, Ball State University", location_details="Room 201" WHERE id="695" OR id="427"');
        $this->execute('UPDATE events SET location="Bracken Library, Ball State University", location_details="Room 215" WHERE id="1166"');
        $this->execute('UPDATE events SET location="Bracken Library, Ball State University" WHERE location="Bracken Library"');

        $this->execute('UPDATE events SET location="Cooper Physical Science Building, Ball State University", location_details="Room 160" WHERE id="1291"');

        $this->execute('UPDATE events SET location="Music Instruction Building, Ball State University", location_details="Room 152" WHERE id="250"');
        $this->execute('UPDATE events SET location="Music Instruction Building, Ball State University" WHERE id="3359" OR id="3371"');
        $this->execute('UPDATE events SET location="Sursa Hall, Ball State University" WHERE id="1164"');

        $this->execute('UPDATE events SET location="Worthen Arena, Ball State University", location_details="Lounge" WHERE id="1290"');
        $this->execute('UPDATE events SET location="Worthen Arena, Ball State University" WHERE id="4449"');

        $this->execute('UPDATE events SET location="Frog Baby, Ball State University" WHERE id="2207"');

        $this->execute('UPDATE events SET location="Letterman Building, Ball State University", location_details="Room 125" WHERE id="526"');

        $this->execute('UPDATE events SET location="Ball Gymnasium, Ball State University" WHERE id="4118"');

        $this->execute('UPDATE events SET location="Alumni Center, Ball State University", location_details="Meeting Room 1" WHERE id="1497" OR id="2967" OR id="3861"');

        $this->execute('UPDATE events SET location="University Green, Ball State University" WHERE id="151"');

        $this->execute('UPDATE events SET location="Multicultural Center, Ball State University" WHERE id="407"');

        $this->execute('UPDATE events SET location="Teachers College, Ball State University", location_details="Room 102" WHERE id="1294"');

        $this->execute('UPDATE events SET location="Student Center, Ball State University" WHERE location="BSU Student Center" OR location="BSU Student Center, Registration on Second Floor" OR id="2021"');
        $this->execute('UPDATE events SET location="Student Center, Ball State University", location_details="Room 310" WHERE location="BSU Student Center room 310" OR id="288"');
        $this->execute('UPDATE events SET location="Student Center, Ball State University", location_details="Forum Room" WHERE location="BSU Student Center, Forum Room"');
        $this->execute('UPDATE events SET location="Student Center, Ball State University", location_details="Room 301" WHERE location="BSU Student Center, Room 301"');
        $this->execute('UPDATE events SET location="Student Center, Ball State University", location_details="Room 303" WHERE location="BSU Student Center, Room 303"');
        $this->execute('UPDATE events SET location="Student Center, Ball State University", location_details="Rooms 310A & B" WHERE location="BSU Student Center, Rooms 310A and B"');
        $this->execute('UPDATE events SET location="Student Center, Ball State University", location_details="Outside" WHERE id="2049"');
        $this->execute('UPDATE events SET location="Student Center, Ball State University", location_details="Ballroom" WHERE id="2234" OR id="2235"');

        $this->execute('UPDATE events SET location="Ball State University (General)" WHERE id="1300"');

        $this->execute('UPDATE events SET location="Multiple Locations" WHERE series_id="275"');

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
