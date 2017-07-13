<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         1.2.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Test\TestCase\Controller;

use App\Controller\PagesController;
use Cake\Core\App;
use Cake\Core\Configure;
use Cake\Network\Request;
use Cake\Network\Response;
use Cake\TestSuite\IntegrationTestCase;
use Cake\View\Exception\MissingTemplateException;
use Facebook\FacebookSession;
use Facebook\FacebookRedirectLoginHelper;

/**
 * ElementsTest class
 */
class ElementsTest extends IntegrationTestCase
{
    /*
     * test that sidebars are populating
     */
    public function testSidebarsLoading()
    {
        $this->get('/');
        $this->assertResponseContains('<div class="categories">');
        $this->assertResponseContains('<div class="locations">');
        $this->assertResponseContains('<div class="tag_cloud">');
    }

    /*
     * test that the calendar is populating dates
     */
    public function testDatepickerIsBeingPopulated()
    {
        $this->get('/');

        // test the datepicker
        $this->assertResponseContains('/events/day/'.date('m'));
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
        $this->get('/events/search?filter=market&direction=future');

        // do the view variables match up?
        $filter =  $this->viewVariable('filter');
        $this->assertEquals('market', $filter['filter']);
        $this->assertEquals('future', $filter['direction']);

        // dateQuery & directionAdjective are constant with $filter->direction
        $this->assertEquals('date >=', $this->viewVariable('dateQuery'));
        $this->assertEquals('upcoming', $this->viewVariable('directionAdjective'));
    }
}
