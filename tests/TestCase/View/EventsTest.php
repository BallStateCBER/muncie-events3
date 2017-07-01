<?php
namespace App\Test\TestCase\Controller;

use App\Controller\CategoriesController;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestCase;

/**
 * App\Controller\CategoriesController Test Case
 */
class EventsViewTest extends IntegrationTestCase
{
    /**
     * Test that events on days are viewable
     *
     * @return void
     */
    public function testDayView()
    {
        $this->get("/events/day/" . date('m') . "/" . date("d") . "/" . date("Y"));
        $this->assertResponseOk();

        $tenDays = range(1, 9);
        if (!in_array(date('d'), $tenDays)) {
            $this->assertResponseContains('Events on ' . date("F d, Y"));
            return;
        }

        $day = date("d");
        $day = str_replace('0', '', $day);

        $this->assertResponseContains('Events on ' . date("F") . ' ' . $day . ', ' . date("Y"));
    }

    /**
     * Test that events on months are viewable
     *
     * @return void
     */
    public function testLocationsIndex()
    {
        $this->get("/location/Be Here Now");
        $this->assertResponseOk();
        $this->assertResponseContains('Be Here Now');
    }

    /**
     * Test that events on months are viewable
     *
     * @return void
     */
    public function testMonthsView()
    {
        $this->get("/events/month/" . date('m') . "/" . date("Y"));
        $this->assertResponseOk();
        $this->assertResponseContains('Events in ' . date("F, Y"));
    }

    /**
     * Test that events on months are viewable
     *
     * @return void
     */
    public function testTagsIndex()
    {
        $this->get("tag/1212_martial-arts?direction=past");
        $this->assertResponseOk();
        $this->assertResponseContains('Tag: Martial Arts');
    }
}
