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
use Cake\Event\Event;

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @link http://book.cakephp.org/3.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller
{

    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * e.g. `$this->loadComponent('Security');`
     *
     * @return void
     */

    public $helpers = ['AkkaCKEditor.CKEditor' =>
        ['distribution' => 'basic'],
        'CakeJs.Js',
        'Flash',
        'Form',
        'Html',
        'iCal'
    ];

    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('Captcha.Captcha');
        $this->loadComponent('RequestHandler');
        $this->loadComponent('Flash');
        $this->loadComponent('Auth', [
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
                ]
            ]
        );
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

        $this->loadModel('Categories');
        $this->loadModel('Events');
        $this->loadModel('Tags');

        $categories = $this->Categories->getAll();

        $results = $this->Events->getFutureEvents();
        $populatedDates = [];
        foreach ($results as $result) {
            $populatedDates[] = $result;
        }

        $this->set([
            'headerVars' => [
                'categories' => $categories,
                'populatedDates' => $populatedDates
            ],
            'sidebarVars' => [
                'locations' => $this->Events->getLocations(),
                'upcomingTags' => $this->Tags->getUpcoming(),
                'upcomingEventsByCategory' => $this->Events->getAllUpcomingEventCounts()
            ],
            'unapprovedCount' => $this->Events->getUnapproved()
        ]);
    }

    // to index events
    public function indexEvents($events)
    {
        foreach ($events as $event) {
            $evDates[] = str_replace(' 00:00:00.000000', '', get_object_vars($event->date));
        }
        foreach ($evDates as $evDate) {
            $dates[] = $evDate['date'];
        }
        // are there multiple events happening on a certain date?
        $multipleDates = false;
        if (count(array_unique($dates))<count($dates)) {
            $multipleDates = true;
            $events = $this->multipleDateIndex($dates, $events);
        }
        if (count(array_unique($dates))>=count($dates)) {
            $events = array_combine($dates, $events);
        }
        $this->set([
            'dates' => $dates,
            'events' => $events,
            'multipleDates' => $multipleDates,
        ]);
    }

        // to index dates with multiple events happening during them
        public function multipleDateIndex($dates, $events)
        {

            // assign each event a date as a key
            foreach ($dates as $i => $k) {
                $events[$k][] = $events[$i];
                unset($events[$i]);
            }

            // if a date has more than one event, add the event to its end, as a new array
            array_walk($events, create_function('&$v',
                '$v = (count($v) == 1)? array_pop($v): $v;'
            ));

            // remove any null or empty events from the array
            $events = array_filter($events, function ($value) {
                return $value !== null;
            });
            return $events;
        }
}
