<?php
namespace App\Test\TestCase;

use App\Application;
use App\Controller\CategoriesController;
use App\Controller\EventsController;
use App\Controller\EventSeriesController;
use App\Controller\ImagesController;
use App\Controller\MailingListController;
use App\Controller\PagesController;
use App\Controller\TagsController;
use Cake\Error\Middleware\ErrorHandlerMiddleware;
use Cake\Http\MiddlewareQueue;
use Cake\ORM\TableRegistry;
use Cake\Routing\Middleware\AssetMiddleware;
use Cake\Routing\Middleware\RoutingMiddleware;
use Cake\TestSuite\IntegrationTestCase;

/**
 * App\Controller\EventsController Test Case
 */
class ApplicationTest extends IntegrationTestCase
{
    public $objects = ['Categories', 'CategoriesMailingList', 'Events', 'EventsImages', 'EventSeries', 'EventsTags', 'Images', 'MailingList', 'Tags', 'Users'];
    public $adminUser;
    public $commoner;
    public $plebian;
    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        foreach ($this->objects as $object) {
            $this->$object = TableRegistry::get($object);
        }

        $this->adminUser = [
            'Auth' => [
                'User' => [
                    'id' => 1,
                    'role' => 'admin'
                ]
            ]
        ];

        $this->commoner = [
            'Auth' => [
                'User' => [
                    'id' => 74,
                    'role' => 'user'
                ]
            ]
        ];

        $this->plebian = [
            'Auth' => [
                'User' => [
                    'id' => 75,
                    'role' => 'user'
                ]
            ]
        ];
    }

    /**
     * testMiddleware
     *
     * @return void
     */
    public function testMiddleware()
    {
        $app = new Application(dirname(dirname(__DIR__)) . '/config');
        $middleware = new MiddlewareQueue();

        $middleware = $app->middleware($middleware);

        $this->assertInstanceOf(ErrorHandlerMiddleware::class, $middleware->get(0));
        $this->assertInstanceOf(AssetMiddleware::class, $middleware->get(1));
        $this->assertInstanceOf(RoutingMiddleware::class, $middleware->get(2));
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        foreach ($this->objects as $object) {
            unset($this->$object);
        }

        parent::tearDown();
    }
}
