<?php
namespace App\Test\TestCase;

use App\Application;
use App\Test\Fixture\EventsFixture;
use App\Test\Fixture\UsersFixture;
use Cake\Error\Middleware\ErrorHandlerMiddleware;
use Cake\Http\MiddlewareQueue;
use Cake\ORM\TableRegistry;
use Cake\Routing\Middleware\AssetMiddleware;
use Cake\Routing\Middleware\RoutingMiddleware;
use Cake\TestSuite\IntegrationTestCase;

/**
 * App\Controller\EventsController Test Case
 *
 * @property \App\Model\Table\CategoriesTable $Categories
 * @property \Cake\ORM\Association\BelongsToMany $CategoriesMailingList
 * @property \App\Model\Table\EventsTable $Events
 * @property \Cake\ORM\Association\BelongsToMany $EventsImages
 * @property \Cake\ORM\Association\BelongsToMany $EventsTags
 * @property \App\Model\Table\EventSeriesTable $EventSeries
 * @property \App\Model\Table\ImagesTable $Images
 * @property \App\Model\Table\MailingListTable $MailingList
 * @property \App\Model\Table\TagsTable $Tags
 * @property \App\Model\Table\UsersTable $Users
 */
class ApplicationTest extends IntegrationTestCase
{
    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.categories',
        'app.categories_mailing_list',
        'app.event_series',
        'app.events',
        'app.events_images',
        'app.events_tags',
        'app.images',
        'app.mailing_list',
        'app.mailing_list_log',
        'app.tags',
        'app.users'
    ];

    public $objects = ['Categories', 'CategoriesMailingList', 'Events', 'EventsImages', 'EventSeries', 'EventsTags', 'Images', 'MailingList', 'MailingListLog', 'Tags', 'Users'];

    // events fixtures
    public $eventInSeries1;
    public $eventInSeries2;
    public $eventInSeries3;
    public $regularEvent;

    // users fixtures
    public $admin;
    public $commoner;

    public $denied = 'You are not authorized to view this page';
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

        $eventsFixture = new EventsFixture();

        $this->eventInSeries1 = $eventsFixture->records[0];
        $this->eventInSeries2 = $eventsFixture->records[1];
        $this->eventInSeries3 = $eventsFixture->records[2];
        $this->regularEvent = $eventsFixture->records[3];

        // set up the users fixtures
        $usersFixture = new UsersFixture();

        $this->admin = [
            'Auth' => [
                'User' => $usersFixture->records[0]
            ]
        ];

        $this->commoner = [
            'Auth' => [
                'User' => $usersFixture->records[1]
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
