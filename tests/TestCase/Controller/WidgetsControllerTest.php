<?php
namespace App\Test\TestCase\Controller;

use App\Controller\WidgetsController;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestCase;

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
        $this->get('/widgets/customize/feed');
        $this->assertResponseOk();

        $feed = [
            'use_custom_categories' => 0,
            'use_custom_locations' => 0,
            'use_custom_tag_include' => 0,
            'use_custom_tag_exclude' => 0,
            'textColorDefault' => '#9966cc',
            'testColorLight' => '#9966cc',
            'textColorLink' => '#9966cc',
            'borderColorLight' => '#9966cc',
            'borderColorDark' => '#9966cc',
            'outerBorder' => 1,
            'backgroundColorDefault' => '#9966cc',
            'backgroundColorAlt' => '#9966cc',
            'height' => '#666px',
            'width' => '#100%'
        ];

        $this->post('/widgets/customize/feed', $feed);
        $this->assertResponseOk();

        #$this->assertResponseContains('feed?textColorLight=%239966cc&amp;textColorLink=%239966cc&amp;borderColorLight=%239966cc&amp;borderColorDark=%239966cc&amp;backgroundColorDefault=%239966cc&amp;backgroundColorAlt=%239966cc');
        #$this->assertResponseContains('<iframe style="height:666px;width:666px;border:1px solid #9966cc;"');
    }

    /**
     * Test month customizer method
     *
     * @return void
     */
    public function testMonthCustomizer()
    {
        $this->get('/widgets/customize/month');
        $this->assertResponseOk();
    }
}
