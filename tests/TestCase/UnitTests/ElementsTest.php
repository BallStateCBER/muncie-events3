<?php
namespace App\Test\TestCase\Controller;

use App\Test\TestCase\ApplicationTest;

class ElementsTest extends ApplicationTest
{
    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
    }
    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();
    }
    /*
     * test that sidebars are populating
     */
    public function testSidebarsLoading()
    {
        $this->get('/');
        $this->assertResponseContains('<div class="categories">');
        $this->assertResponseContains('<div class="locations">');
        $this->assertResponseContains('<a href="/tags" class="see_all">');
        $this->assertResponseContains('<div id="sidebar_mailinglist">');
        $this->assertResponseContains('<div id="sidebar_widget">');
    }
    /*
     * test that the calendar is populating dates
     */
    public function testDatepickerIsBeingPopulated()
    {
        $this->get('/');
        // test the datepicker
        $this->assertResponseContains('/events/day/');
    }
    /*
     * test header links when logged out
     */
    public function testUnauthenticatedHeaderFunctions()
    {
        $this->get('/');
        $this->assertResponseContains('<a href="/login"');
        $this->assertResponseContains('<a href="/register"');
    }
    /*
     * test header links when logged in
     */
    public function testAuthenticatedHeaderFunctions()
    {
        $this->session(['Auth.User.Id' => 1]);
        $this->get('/');
        // test user links populate
        $this->assertResponseContains('<a href="/account"');
        $this->assertResponseContains('<a href="/logout"');
    }
    /*
     * test that search filters actually filter search
     */
    public function testThatSearchFilterParamsPass()
    {
        $this->get('/events/search?filter=market&direction=upcoming');
        // do the view variables match up?
        $filter = $this->viewVariable('filter');
        $this->assertEquals('market', $filter['filter']);
        $this->assertEquals('upcoming', $filter['direction']);
        // dateQuery & directionAdjective are constant with $filter->direction
        $this->assertEquals('start >=', $this->viewVariable('dateQuery'));
        $this->assertEquals('upcoming', $this->viewVariable('directionAdjective'));
    }
}
