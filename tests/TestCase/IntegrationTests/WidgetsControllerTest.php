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
        $dummyEvent = [
            'title' => 'Widget!',
            'category_id' => 1,
            'date' => date('m/d/Y'),
            'time_start' => [
                'hour' => '12',
                'minute' => '00',
                'meridian' => 'am'
            ],
            'time_end' => [
                'hour' => '01',
                'minute' => '00',
                'meridian' => 'am'
            ],
            'location' => 'PlaceholderTown',
            'location_details' => 'Room 6',
            'address' => '666 Placeholder Place',
            'description' => 'Come out with my support!',
            'cost' => '$6',
            'age_restriction' => '66 or younger',
            'source' => 'Placeholder Digest Tri-Weekly',
            'data' => [
                'Tags' => [
                    1
                ]
            ]
        ];
        $this->post('/events/add', $dummyEvent);
        $this->assertResponseSuccess();
        $dummyEvent = [
            'title' => 'Twidget!',
            'category_id' => 2,
            'date' => date('m/d/Y'),
            'time_start' => [
                'hour' => '12',
                'minute' => '00',
                'meridian' => 'am'
            ],
            'time_end' => [
                'hour' => '01',
                'minute' => '00',
                'meridian' => 'am'
            ],
            'location' => 'PlaceholderTown',
            'location_details' => 'Room 6',
            'address' => '666 Placeholder Place',
            'description' => 'Come out with my support!',
            'cost' => '$6',
            'age_restriction' => '66 or younger',
            'source' => 'Placeholder Digest Tri-Weekly',
            'data' => [
                'Tags' => [
                    1
                ]
            ]
        ];
        $this->post('/events/add', $dummyEvent);
        $this->assertResponseSuccess();
        $dummyEvent = [
            'title' => 'Twidget!',
            'category_id' => 1,
            'date' => date('m/d/Y'),
            'time_start' => [
                'hour' => '12',
                'minute' => '00',
                'meridian' => 'am'
            ],
            'time_end' => [
                'hour' => '01',
                'minute' => '00',
                'meridian' => 'am'
            ],
            'location' => 'PlaceholderTown',
            'location_details' => 'Room 6',
            'address' => '666 Placeholder Place',
            'description' => 'Come out with my support!',
            'cost' => '$6',
            'age_restriction' => '66 or younger',
            'source' => 'Placeholder Digest Tri-Weekly',
            'data' => [
                'Tags' => [
                    1
                ]
            ]
        ];
        $this->post('/events/add', $dummyEvent);
        $this->assertResponseSuccess();
        $dummyEvent = [
            'title' => 'Twidget!',
            'category_id' => 2,
            'date' => date('m/d/Y'),
            'time_start' => [
                'hour' => '12',
                'minute' => '00',
                'meridian' => 'am'
            ],
            'time_end' => [
                'hour' => '01',
                'minute' => '00',
                'meridian' => 'am'
            ],
            'location' => 'Mr. Placeholder\'s Casa',
            'location_details' => 'Room 6',
            'address' => '666 Placeholder Place',
            'description' => 'Come out with my support!',
            'cost' => '$6',
            'age_restriction' => '66 or younger',
            'source' => 'Placeholder Digest Tri-Weekly',
            'data' => [
                'Tags' => [
                    1
                ]
            ]
        ];
        $this->post('/events/add', $dummyEvent);
        $this->assertResponseSuccess();
        $this->get('/widgets/feed?', [
            'category' => 2,
            'location' => 'placeholdertown',
            'tags_included' => 'holding places',
            'tags_excluded' => ''
        ]);
        $this->assertResponseOk();
        $iframeQueryString = $this->viewVariable('iframeQueryString');
        $this->markTestIncomplete();
        #$this->assertEquals('category=2&location=placeholdertown&tags_included=holding%20places', $iframeQueryString);
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
