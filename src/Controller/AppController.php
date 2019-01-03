<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link      http://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Routing\Router;

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
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
 *
 * @link http://book.cakephp.org/3.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller
{
    public $autoComplete = ['searchAutoComplete', 'searchAutocomplete', 'autoComplete'];

    public $helpers = [
        'AkkaCKEditor.CKEditor' => [
            'distribution' => 'basic',
            'local_plugin' => [
                'emojione' => [
                    'location' => WWW_ROOT . 'emojione' . DS . 'lib' . DS . 'js',
                    'file' => 'emojione.js'
                ]
            ],
            'version' => '4.5.0'
        ],
        'CakeJs.Js',
        'Flash',
        'Form',
        'Html'
    ];

    public $paginate = [
        'limit' => 15,
        'order' => [
            'title' => 'desc'
        ]
    ];

    /**
     * Initialization hook method.
     *
     * @return void
     * @throws \Exception
     */
    public function initialize()
    {
        parent::initialize();

        $this->loadComponent('Paginator');
        $this->loadComponent('RequestHandler');
        $this->loadComponent('Flash');
        $this->loadComponent(
            'Auth',
            [
                'loginAction' => [
                    'prefix' => false,
                    'controller' => 'Users',
                    'action' => 'login'
                ],
                'logoutRedirect' => [
                    'prefix' => false,
                    'controller' => 'Events',
                    'action' => 'index'
                ],
                'authenticate' => [
                    'Form' => [
                        'fields' => [
                            'username' => 'email',
                            'password' => 'password'
                        ],
                        'passwordHasher' => [
                            'className' => 'Fallback',
                            'hashers' => [
                                'Default',
                                'Weak' => ['hashType' => 'sha1']
                            ]
                        ]
                    ],
                    'Cookie' => [
                        'fields' => [
                            'username' => 'email',
                            'password' => 'password'
                        ]
                    ]
                ],
                'authError' => 'You are not authorized to view this page',
                'authorize' => 'Controller'
            ]
        );

        if (php_sapi_name() != 'cli') {
            $this->loadComponent('Security', [
                'blackHoleCallback' => 'forceSSL',
                'validatePost' => false
            ]);
        }

        $this->loadModel('Categories');
        $this->loadModel('CategoriesMailingList');
        $this->loadModel('Events');
        $this->loadModel('EventSeries');
        $this->loadModel('EventsImages');
        $this->loadModel('EventsTags');
        $this->loadModel('Images');
        $this->loadModel('MailingList');
        $this->loadModel('Tags');
        $this->loadModel('Users');
    }

    /**
     * beforeFilter event
     *
     * @param Event $event Event object
     * @return void
     */
    public function beforeFilter(Event $event)
    {
        if (php_sapi_name() != 'cli') {
            $this->Security->requireSecure();
        }

        if (!$this->Auth->user() && $this->request->getCookie('CookieAuth')) {
            $user = $this->Auth->identify();
            if ($user) {
                $this->Auth->setUser($user);
            } else {
                $this->response = $this->response->withExpiredCookie('CookieAuth');
            }
        }
    }

    /**
     * forceSSL function
     *
     * @return \Cake\Http\Response|null
     */
    public function forceSSL()
    {
        if (php_sapi_name() != 'cli') {
            return $this->redirect(env('FULL_BASE_URL') . $this->request->getRequestTarget());
        }

        return null;
    }

    /**
     * Before render callback.
     *
     * @param \Cake\Event\Event $event The beforeRender event.
     * @return \Cake\Network\Response|null|void
     */
    public function beforeRender(Event $event)
    {
        if (!array_key_exists('_serialize', $this->viewBuilder()->getVars()) &&
            in_array($this->response->getType(), ['application/json', 'application/xml'])
        ) {
            $this->set('_serialize', true);
        }

        $results = $this->Events->getPopulatedDates();
        $populated = [];
        foreach ($results as $result) {
            list($year, $month, $day) = explode('-', $result);
            $populated["$month-$year"][] = $day;
        }

        if (!in_array($this->request->getParam('action'), $this->autoComplete)) {
            $this->set($this->getLayoutVars());
            $this->set([
                'populated' => $populated,
                'unapprovedCount' => $this->Events->getUnapproved(),
                'recentUsersCount' => $this->Users->getRecentUsersCount(),
                'getActive' => function ($controller, $action) {
                    if ($this->request->getParam('controller') != $controller) {
                        return null;
                    }
                    if ($this->request->getParam('action') != $action) {
                        return null;
                    }
                    return 'active';
                },
                'fullBaseUrl' => Configure::read('App.fullBaseUrl')
            ]);
        }
    }

    /**
     * Sets view variables needed to display an index of events
     *
     * @param array|\Cake\ORM\ResultSet $events Collection of events to display
     * @return void
     */
    public function indexEvents($events)
    {
        foreach ($events as $event) {
            /** @var \App\Model\Entity\Event $event */
            $event = $this->Events->setEasternTimes($event);
            $dates[] = $event->date->format('Y-m-d');
        }

        // Are there multiple events happening on a certain date?
        $multipleDates = false;
        if (isset($dates)) {
            if (count(array_unique($dates)) < count($dates)) {
                $multipleDates = true;
                $events = $this->multipleDateIndex($dates, $events);
            }
            if (count(array_unique($dates)) >= count($dates)) {
                $events = array_combine($dates, $events);
            }
            $nextStartDate = $this->Events->getNextStartDate($dates);
            $prevStartDate = $this->Events->getPrevStartDate($dates);
        }
        $this->set(compact('dates', 'events', 'multipleDates', 'nextStartDate', 'prevStartDate'));
    }

    /**
     * to index events for Json array
     *
     * @param \App\Model\Entity\Event $events Event entities
     * @return void
     */
    public function getEventsForJson($events)
    {
        $eventsForJson = [];
        foreach ($events as $date => $daysEvents) {
            if (!isset($eventsForJson[$date])) {
                $eventsForJson[$date] = [
                    'heading' => 'Events on ' . date('F j, Y', (strtotime($date))),
                    'events' => []
                ];
            }
            if (isset($daysEvents[0])) {
                foreach ($daysEvents as $daysEvent) {
                    $this->setJsonArrayPr($date, $daysEvent);
                }
            } else {
                $this->setJsonArrayPr($date, $daysEvents);
            }
        }
    }

    /**
     * to set Json array
     *
     * @param string $date date string
     * @param \App\Model\Entity\Event $daysEvents Events entities
     * @return void
     */
    private function setJsonArrayPr($date, $daysEvents)
    {
        $timestamp = strtotime($daysEvents->time_start->i18nFormat('yyyyMMddHHmmss'));
        $displayedTime = date('g:ia', $timestamp);
        $daysEvents['displayed_time'] = $displayedTime;
        $eventsForJson[$date]['events'][] = [
            'id' => $daysEvents->id,
            'title' => $daysEvents->title,
            'category_name' => $daysEvents->category->name,
            'category_icon_class' => 'icon-' . strtolower(str_replace(' ', '-', $daysEvents->category->name)),
            'url' => Router::url(['controller' => 'Events', 'action' => 'view', 'id' => $daysEvents->id]),
            'time' => $displayedTime
        ];
        $this->set(compact('eventsForJson'));
    }

    /**
     * to index dates with multiple events happening during them
     *
     * @param array $dates This is an array full of date objects
     * @param array $events This is an array of various Events entities
     * @return \App\Model\Entity\Event $events Events entities with new multiplicity
     */
    public function multipleDateIndex($dates, $events)
    {
        // assign each event a date as a key
        foreach ($dates as $i => $k) {
            $events[$k][] = $events[$i];
            unset($events[$i]);
        }

        // if a date has more than one event, add the event to its end, as a new array
        array_walk(
            $events,
            function (&$v) {
                $v = (count($v) == 1) ? array_pop($v): $v;
            }
        );

        // remove any null or empty events from the array
        $events = array_filter(
            $events,
            function ($value) {
                return $value !== null;
            }
        );

        return $events;
    }

    /**
     * Returns variables used in the sidebar and header
     *
     * @return array
     */
    private function getLayoutVars()
    {
        $locations = $this->Events->getUpcomingLocationsWithSlugs();
        $upcomingTags = $this->Tags->getUpcoming();

        $categories = $this->getCategoriesForSidebar();
        $populatedDates = $this->getPopulatedDates();
        $dayLinks = $this->getDayLinksForHeader($populatedDates);

        return [
            'sidebarVars' => compact('locations', 'upcomingTags', 'categories'),
            'headerVars' => compact('populatedDates', 'dayLinks')
        ];
    }

    /**
     * Returns an array of categories and extra information used to display them in the sidebar
     *
     * @return array
     */
    private function getCategoriesForSidebar()
    {
        $upcomingEventsByCategory = $this->Events->getAllUpcomingEventCounts();
        $categories = $this->Categories->find('all', [
            'order' => ['weight' => 'ASC']
        ])->toArray();
        foreach ($categories as &$category) {
            $category['url'] = Router::url([
                'plugin' => false,
                'prefix' => false,
                'controller' => 'events',
                'action' => 'category',
                $category['slug']
            ]);
            $categoryId = $category['id'];
            $category['upcomingEventsCount'] = $upcomingEventsByCategory[$categoryId] ?? 0;
            $category['upcomingEventsTitle'] = sprintf(
                '%s upcoming %s',
                $category['upcomingEventsCount'],
                __n('event', 'events', $category['upcomingEventsCount'])
            );
        }

        return $categories;
    }

    /**
     * Returns an array of dates that contain events
     *
     * @return array
     */
    private function getPopulatedDates()
    {
        $results = $this->Events->getFutureEvents();
        $populatedDates = [];
        foreach ($results as $result) {
            if (!in_array($result, $populatedDates)) {
                $populatedDates[] = $result;
            }
        }

        return $populatedDates;
    }

    /**
     * Returns a maximum of seven links to the soonest dates with upcoming events
     *
     * @param array $populatedDates Array of populated dates
     * @return array
     */
    private function getDayLinksForHeader($populatedDates)
    {
        $dayLinks = [];
        foreach ($populatedDates as $date) {
            $dateStringYMD = $date[4] . '-' . $date[2] . '-' . $date[3];
            if ($dateStringYMD == date('Y-m-d')) {
                $dayLinks[] = [
                    'label' => 'Today',
                    'url' => Router::url([
                        'plugin' => false,
                        'prefix' => false,
                        'controller' => 'events',
                        'action' => 'today'
                    ])
                ];
                continue;
            }

            if ($dateStringYMD == date('Y-m-d', strtotime('Tomorrow'))) {
                $dayLinks[] = [
                    'label' => 'Tomorrow',
                    'url' => Router::url([
                        'plugin' => false,
                        'prefix' => false,
                        'controller' => 'events',
                        'action' => 'tomorrow'
                    ])
                ];
                continue;
            }
            $dayLinks[] = [
                'label' => sprintf(
                    '%s, %s %s',
                    $date[0],
                    $date[1],
                    $date[3]
                ),
                'url' => Router::url([
                    'plugin' => false,
                    'prefix' => false,
                    'controller' => 'events',
                    'action' => 'day',
                    $date[2],
                    $date[3],
                    $date[4]
                ])
            ];
            if (count($dayLinks) == 7) {
                break;
            }
        }

        return $dayLinks;
    }
}
