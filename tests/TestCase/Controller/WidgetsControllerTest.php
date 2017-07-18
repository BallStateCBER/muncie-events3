<?php
namespace App\Test\TestCase\Controller;

use App\Controller\WidgetsController;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Cake\TestSuite\IntegrationTestCase;
use Facebook\FacebookSession;
use Facebook\FacebookRedirectLoginHelper;

/**
 * App\Controller\WidgetsController Test Case
 */
class WidgetsControllerTest extends IntegrationTestCase
{
    /**
     * Test feed customizer method
     *
     * @return void
     */
    public function testFeedCustomizer()
    {
        $this->Events = TableRegistry::get('Events');

        $dummyEvent = [
            'title' => 'Widget!',
            'category_id' => 13,
            'date' => date('m/d/Y'),
            'time_start' => date('Y-m-d'),
            'time_end' => strtotime('+1 hour'),
            'location' => 'PlaceholderTown',
            'location_details' => 'Room 6',
            'address' => '666 Placeholder Place',
            'description' => 'Come out with my support!',
            'cost' => '$6',
            'age_restriction' => '66 or younger',
            'source' => 'Placeholder Digest Tri-Weekly',
            'data' => [
                'Tags' => [
                    527
                ]
            ]
        ];

        $this->post('/events/add', $dummyEvent);
        $this->assertResponseSuccess();

        $dummyEvent = [
            'title' => 'Twidget!',
            'category_id' => 9,
            'date' => date('m/d/Y'),
            'time_start' => date('Y-m-d'),
            'time_end' => strtotime('+1 hour'),
            'location' => 'PlaceholderTown',
            'location_details' => 'Room 6',
            'address' => '666 Placeholder Place',
            'description' => 'Come out with my support!',
            'cost' => '$6',
            'age_restriction' => '66 or younger',
            'source' => 'Placeholder Digest Tri-Weekly',
            'data' => [
                'Tags' => [
                    527
                ]
            ]
        ];

        $this->post('/events/add', $dummyEvent);
        $this->assertResponseSuccess();

        $dummyEvent = [
            'title' => 'Twidget!',
            'category_id' => 13,
            'date' => date('m/d/Y'),
            'time_start' => date('Y-m-d'),
            'time_end' => strtotime('+1 hour'),
            'location' => 'PlaceholderTown',
            'location_details' => 'Room 6',
            'address' => '666 Placeholder Place',
            'description' => 'Come out with my support!',
            'cost' => '$6',
            'age_restriction' => '66 or younger',
            'source' => 'Placeholder Digest Tri-Weekly',
            'data' => [
                'Tags' => [
                    527, 528
                ]
            ]
        ];

        $this->post('/events/add', $dummyEvent);
        $this->assertResponseSuccess();

        $dummyEvent = [
            'title' => 'Twidget!',
            'category_id' => 13,
            'date' => date('m/d/Y'),
            'time_start' => date('Y-m-d'),
            'time_end' => strtotime('+1 hour'),
            'location' => 'Mr. Placeholder\'s Casa',
            'location_details' => 'Room 6',
            'address' => '666 Placeholder Place',
            'description' => 'Come out with my support!',
            'cost' => '$6',
            'age_restriction' => '66 or younger',
            'source' => 'Placeholder Digest Tri-Weekly',
            'data' => [
                'Tags' => [
                    527
                ]
            ]
        ];

        $this->post('/events/add', $dummyEvent);
        $this->assertResponseSuccess();

        $this->get('/widgets/feed' . '?category=13&location=placeholdertown&tags_included=potluck&tags_excluded=slow+food');
        $this->assertResponseOk();

        $iframeQueryString =  $this->viewVariable('iframeQueryString');
        $this->assertEquals('category=13&location=placeholdertown&tags_included=potluck&tags_excluded=slow+food', $iframeQueryString);
    }

    /**
     * Test month customizer method
     *
     * @return void
     */
    public function testMonthCustomizer()
    {
        $this->get('/widgets/month' . '?hideGeneralEventsIcon=1&category=13&location=placeholdertown&tags_included=potluck&tags_excluded=slow+food');
        $this->assertResponseOk();

        $iframeQueryString =  $this->viewVariable('iframeQueryString');
        $this->assertEquals('hideGeneralEventsIcon=1&category=13&location=placeholdertown&tags_included=potluck&tags_excluded=slow+food', $iframeQueryString);

        $dummies = $this->Events->find()
            ->where(['title' => 'Widget!'])
            ->orWhere(['title' => 'Twidget!']);

        foreach ($dummies as $dummy) {
            $this->Events->delete($dummy);
        }
    }
}
