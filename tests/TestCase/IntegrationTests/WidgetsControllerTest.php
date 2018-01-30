<?php
namespace App\Test\TestCase\Controller;

use App\Test\TestCase\ApplicationTest;

class WidgetsControllerTest extends ApplicationTest
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
    /**
     * Test feed customizer method
     *
     * @return void
     */
    public function testFeedCustomizer()
    {
        $this->get([
            'controller' => 'Widgets',
            'action' => 'feed',
            '?' => [
                'category' => 2,
                'location' => 'placeholdertown',
                'tags_included' => 'holding places',
                'tags_excluded' => ''
            ]
        ]);
        $this->assertResponseOk();
    }
    /**
     * Test month customizer method
     *
     * @return void
     */
    public function testMonthCustomizer()
    {
        $this->get('/widgets/month' . '?hideGeneralEventsIcon=1&category=2&location=placeholdertown&tags_included=holding%20places');
        $this->assertResponseOk();
        /*    $iframeQueryString =  $this->viewVariable('iframeQueryString');
            $this->assertEquals('hideGeneralEventsIcon=1&category=13&location=placeholdertown&tags_included=potluck&tags_excluded=slow+food', $iframeQueryString); */
        $dummies = $this->Events->find()
            ->where(['title' => 'Widget!'])
            ->orWhere(['title' => 'Twidget!']);
        foreach ($dummies as $dummy) {
            $this->Events->delete($dummy);
        }
        $this->markTestIncomplete();
    }
}
