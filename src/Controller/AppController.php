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
                    'location' => WWW_ROOT . 'js',
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
     */
    public function initialize()
    {
        parent::initialize();

        $this->loadComponent('Paginator');
        $this->loadComponent('RequestHandler');
        $this->loadComponent('Flash');
        $this->loadComponent('Cookie');
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
            $this->loadComponent('Captcha.Captcha');
            /*if (!in_array($this->request->getParam('action'), $this->autoComplete)) {
                $this->loadComponent('AkkaFacebook.Graph', [
                    'app_id' => '496726620385625',
                    'app_secret' => '8c2bca1961dbf8c8bb92484d9d2dd318',
                    'app_scope' => 'email,public_profile', // https://developers.facebook.com/docs/facebook-login/permissions/v2.4
                    'redirect_url' => Router::url(['controller' => 'Users', 'action' => 'login'], true), // This should be enabled by default
                    'post_login_redirect' => '/', //ie. Router::url(['controller' => 'Users', 'action' => 'account'], TRUE)
                ]);
            }*/
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
    }

    /**
     * Before render callback.
     *
     * @param \Cake\Event\Event $event The beforeRender event.
     * @return \Cake\Network\Response|null|void
     */
    public function beforeRender(Event $event)
    {
        if (!array_key_exists('_serialize', $this->viewVars) &&
            in_array($this->response->type(), ['application/json', 'application/xml'])
        ) {
            $this->set('_serialize', true);
        }

        $categories = $this->Categories->find('all', [
           'order' => ['weight' => 'ASC']
        ])->toArray();

        $results = $this->Events->getFutureEvents();
        $populatedDates = [];
        foreach ($results as $result) {
            if (!in_array($result, $populatedDates)) {
                $populatedDates[] = $result;
            }
        }

        $results = $this->Events->getPopulatedDates();
        $populated = [];
        foreach ($results as $result) {
            list($year, $month, $day) = explode('-', $result);
            $populated["$month-$year"][] = $day;
        }

        $fullBaseUrl = Configure::read('App.fullBaseUrl');

        if (!in_array($this->request->getParam('action'), $this->autoComplete)) {
            $this->set([
                'headerVars' => [
                    'categories' => $categories,
                    'populatedDates' => $populatedDates
                ],
                'populated' => $populated,
                'sidebarVars' => [
                    'locations' => $this->Events->getLocations(),
                    'upcomingTags' => $this->Tags->getUpcoming(),
                    'upcomingEventsByCategory' => $this->Events->getAllUpcomingEventCounts()
                ],
                'unapprovedCount' => $this->Events->getUnapproved()
            ]);
            $this->set(compact('fullBaseUrl'));
        }
    }

    /**
     * to index events
     * function placed in AppController so as to be shared by all controllers
     *
     * @param array|\Cake\ORM\ResultSet $events that need indexed
     * @return void
     */
    public function indexEvents($events)
    {
        $this->loadModel('Events');
        foreach ($events as $event) {
            $event = $this->Events->setEasternTimes($event);
            $dates[] = $event['start']->format('Y-m-d');
        }
        // are there multiple events happening on a certain date?
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
            create_function(
                '&$v',
                '$v = (count($v) == 1)? array_pop($v): $v;'
            )
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
}
