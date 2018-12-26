<?php
namespace App\Test\TestCase;

use App\Application;
use App\Test\Fixture\EventsFixture;
use App\Test\Fixture\MailingListFixture;
use App\Test\Fixture\UsersFixture;
use Cake\Cache\Cache;
use Cake\Error\Middleware\ErrorHandlerMiddleware;
use Cake\Http\MiddlewareQueue;
use Cake\ORM\TableRegistry;
use Cake\Routing\Middleware\AssetMiddleware;
use Cake\Routing\Middleware\RoutingMiddleware;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use InvalidArgumentException;

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
class ApplicationTest extends TestCase
{
    use IntegrationTestTrait;

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

    public $objects = [
        'Categories',
        'CategoriesMailingList',
        'Events',
        'EventsImages',
        'EventSeries',
        'EventsTags',
        'Images',
        'MailingList',
        'MailingListLog',
        'Tags',
        'Users'
    ];

    // events fixtures
    public $eventInSeries1;
    public $eventInSeries2;
    public $eventInSeries3;
    public $regularEvent;

    // mailing list fixtures
    public $weeklyMailingList;
    public $dailyMailingList;

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
        $this->configRequest([
            'environment' => ['HTTPS' => 'on']
        ]);
        foreach ($this->objects as $object) {
            $this->$object = TableRegistry::getTableLocator()->get($object);
        }

        $eventsFixture = new EventsFixture();

        $this->eventInSeries1 = $eventsFixture->records[0];
        $this->eventInSeries2 = $eventsFixture->records[1];
        $this->eventInSeries3 = $eventsFixture->records[2];
        $this->regularEvent = $eventsFixture->records[3];

        $mailingListFixture = new MailingListFixture();
        $this->weeklyMailingList = $mailingListFixture->records[0];
        $this->dailyMailingList = $mailingListFixture->records[1];

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

        Cache::clear(false);
    }

    /**
     * testBootstrap
     *
     * @return void
     */
    public function testBootstrap()
    {
        $app = new Application(dirname(dirname(__DIR__)) . '/config');
        $app->bootstrap();
        $plugins = $app->getPlugins();

        $this->assertCount(3, $plugins);
        $this->assertSame('Bake', $plugins->get('Bake')->getName());
        $this->assertSame('Migrations', $plugins->get('Migrations')->getName());
        $this->assertSame('DebugKit', $plugins->get('DebugKit')->getName());
    }

    /**
     * testBootstrapPluginWitoutHalt
     *
     * @return void
     */
    public function testBootstrapPluginWitoutHalt()
    {
        $this->expectException(InvalidArgumentException::class);

        $app = $this->getMockBuilder(Application::class)
            ->setConstructorArgs([dirname(dirname(__DIR__)) . '/config'])
            ->setMethods(['addPlugin'])
            ->getMock();

        $app->method('addPlugin')
            ->will($this->throwException(new InvalidArgumentException('test exception.')));

        $app->bootstrap();
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
        Cache::clear(false);
    }
}
