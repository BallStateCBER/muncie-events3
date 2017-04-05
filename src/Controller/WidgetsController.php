<?php
namespace app\Controller;

class WidgetsController extends AppController
{
    public $name = 'Widgets';
    public $uses = array('Event', 'Widget');
    public $components = array();
    public $helpers = array();

    public $customStyles = array();

    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);
        $this->Auth->allow();
    }

    public function beforeRender(Event $event)
    {
        parent::beforeRender($event);
    }

    private function __setDemoData($widgetType)
    {
        $this->Widget->setType($widgetType);
        $iframeQueryString = $this->Widget->getIframeQueryString();
        $options = $this->Widget->getOptions();
        $iframeStyles = $this->Widget->getIframeStyles($options);
        $this->set(array(
            'defaults' => $this->Widget->getDefaults(),
            'iframeStyles' => $iframeStyles,
            'iframeUrl' => Router::url(array(
                'controller' => 'widgets',
                'action' => $widgetType,
                '?' => $iframeQueryString
            ), true),
            'code_url' => Router::url(array(
                'controller' => 'widgets',
                'action' => $widgetType,
                '?' => str_replace('&', '&amp;', $iframeQueryString)
            ), true),
            'categories' => $this->Event->Category->getAll()
        ));
    }

    /**
     * Produces a view that lists seven event-populated days, starting with $startDate
     * @param string $startDate 'yyyy-mm-dd', today by default
     */
    public function feed($startDate = null)
    {
        $this->__setDemoData('feed');

        // Get relevant event filters
        $options = $_GET;
        $filters = $this->Event->getValidFilters($options);
        $events = $this->Event->getWidgetPage($startDate, $filters);
        $eventIds = array();
        foreach ($events as $date => $daysEvents) {
            foreach ($daysEvents as $event) {
                $eventIds[] = $event['Event']['id'];
            }
        }
        $this->layout = $this->request->is('ajax') ? 'Widgets'.DS.'ajax' : 'Widgets'.DS.'feed';
        $this->Widget->processCustomStyles($options);

        // $_SERVER['QUERY_STRING'] includes the base url in AJAX requests for some reason
        $baseUrl = Router::url(array('controller' => 'widgets', 'action' => 'feed'), true);
        $queryString = str_replace($baseUrl, '', $_SERVER['QUERY_STRING']);

        $this->set(array(
            'titleForLayout' => 'Upcoming Events',
            'events' => $events,
            'eventIds' => $eventIds,
            'is_ajax' => $this->request->is('ajax'),
            'nextStartDate' => $this->Event->getNextStartDate($events),
            'customStyles' => $this->Widget->customStyles,
            'filters' => $filters,
            'categories' => $this->Event->Category->getList(),
            'all_events_url' => $this->__getAllEventsUrl('feed', $queryString)
        ));
    }

    /**
     * Produces a grid-calendar view for the provided month
     * @param string $month 'yyyy-mm', current month by default
     */
    public function month($yearMonth = null)
    {
        $this->__setDemoData('month');

        // Process various date information
        if (!$yearMonth) {
            $yearMonth = date('Y-m');
        }
        $split = explode('-', $yearMonth);
        $year = reset($split);
        $month = end($split);
        $timestamp = mktime(0, 0, 0, $month, 1, $year);
        $monthName = date('F', $timestamp);
        $preSpacer = date('w', $timestamp);
        $lastDay = date('t', $timestamp);
        $postSpacer = 6 - date('w', mktime(0, 0, 0, $month, $lastDay, $year));
        $prevYear = ($month == 1) ? $year - 1 : $year;
        $prevMonth = ($month == 1) ? 12 : $month - 1;
        $nextYear = ($month == 12) ? $year + 1 : $year;
        $nextMonth = ($month == 12) ? 1 : $month + 1;
        $today = date('Y').date('m').date('j');

        // Get relevant event filters
        $options = $_GET;
        $filters = $this->Event->getValidFilters($options);
        $events = $this->Event->getMonth($yearMonth, $filters);
        $eventsForJson = array();
        foreach ($events as $date => &$daysEvents) {
            if (!isset($eventsForJson[$date])) {
                $eventsForJson[$date] = array(
                    'heading' => 'Events on '.date('F j, Y', strtotime($date)),
                    'events' => array()
                );
            }
            foreach ($daysEvents as &$event) {
                $timeSplit = explode(':', $event['Event']['time_start']);
                $timestamp = mktime($timeSplit[0], $timeSplit[1]);
                $displayedTime = date('g:ia', $timestamp);
                $event['Event']['displayed_time'] = $displayedTime;
                $eventsForJson[$date]['events'][] = array(
                    'id' => $event['Event']['id'],
                    'title' => $event['Event']['title'],
                    'category_name' => $event['Category']['name'],
                    'category_icon_class' => 'icon-'.strtolower(str_replace(' ', '-', $event['Category']['name'])),
                    'url' => Router::url(array('controller' => 'events', 'action' => 'view', 'id' => $event['Event']['id'])),
                    'time' => $displayedTime
                );
            }
        }
        $this->layout = $this->request->is('ajax') ? 'Widgets'.DS.'ajax' : 'Widgets'.DS.'month';
        $this->Widget->processCustomStyles($options);

        // Events displayed per day
        if (isset($options['events_displayed_per_day'])) {
            $eventsDisplayedPerDay = $options['events_displayed_per_day'];
        } else {
            $defaults = $this->Widget->getDefaults();
            $eventsDisplayedPerDay = $defaults['event_options']['events_displayed_per_day'];
        }

        // $_SERVER['QUERY_STRING'] includes the base url in AJAX requests for some reason
        $baseUrl = Router::url(array('controller' => 'widgets', 'action' => 'month'), true);
        $queryString = str_replace($baseUrl, '', $_SERVER['QUERY_STRING']);

        $this->set(array(
            'titleForLayout' => "$monthName $year",
            'events_displayed_per_day' => $eventsDisplayedPerDay,
            'customStyles' => $this->Widget->customStyles,
            'all_events_url' => $this->__getAllEventsUrl('month', $queryString),
            'categories' => $this->Event->Category->getList()
        ));
        $this->set(compact(
            'month', 'year', 'timestamp', 'monthName', 'preSpacer', 'lastDay', 'postSpacer',
            'prevYear', 'prevMonth', 'nextYear', 'nextMonth', 'today', 'events',
            'eventsForJson', 'filters'
        ));
    }

    /**
     * Loads a list of all events on a given day, used by the month widget
     * @param int $year Format: yyyy
     * @param int $month Format: mm
     * @param int $day Format: dd
     */
    public function day($year, $month, $day)
    {
        $month = str_pad($month, 2, '0', STR_PAD_LEFT);
        $day = str_pad($month, 2, '0', STR_PAD_LEFT);
        $options = $_GET;
        $filters = $this->Event->getValidFilters($options);
        $events = $this->Event->getFilteredEventsOnDates("$year-$month-$day", $filters, true);
        $this->set(array(
            'titleForLayout' => 'Events on '.date('F jS, Y', mktime(0, 0, 0, $month, $day, $year)),
            'events' => $events
        ));
    }

    /**
     * Accepts a query string and returns the URL to view this calendar with no filters (but custom styles retained)
     * @param string $queryString
     * @return string
     */
    private function __getAllEventsUrl($action, $queryString)
    {
        if (empty($queryString)) {
            $new_queryString = '';
        } else {
            $parameters = explode('&', urldecode($queryString));
            $filteredParams = array();
            $defaults = $this->Widget->getDefaults();
            foreach ($parameters as $paramPair) {
                $pairSplit = explode('=', $paramPair);
                list($var, $val) = $pairSplit;
                if (!isset($defaults['event_options'][$var])) {
                    $filteredParams[$var] = $val;
                }
            }
            $new_queryString = http_build_query($filteredParams, '', '&amp;');
        }
        return Router::url(array(
            'controller' => 'widgets',
            'action' => $action,
            '?' => $new_queryString
        ));
    }

    public function event($id)
    {
        $event = $this->Event->find('first', array(
            'conditions' => array('Event.id' => $id),
            'contain' => array(
                'User' => array(
                    'fields' => array('User.id', 'User.name')
                ),
                'Category' => array(
                    'fields' => array('Category.id', 'Category.name', 'Category.slug')
                ),
                'EventSeries' => array(
                    'fields' => array('EventSeries.id', 'EventSeries.title')
                ),
                'Tag' => array(
                    'fields' => array('Tag.id', 'Tag.name')
                ),
                'EventsImage' => array(
                    'fields' => array('EventsImage.id', 'EventsImage.caption'),
                    'Image' => array(
                        'fields' => array('Image.id', 'Image.filename')
                    )
                )
            )
        ));

        if (empty($event)) {
            return $this->renderMessage(array(
                'title' => 'Event Not Found',
                'message' => "Sorry, but we couldn't find the event (#$id) you were looking for.",
                'class' => 'error'
            ));
        }
        $this->layout = $this->request->is('ajax') ? 'Widgets'.DS.'ajax' : 'Widgets'.DS.'feed';
        $this->set(array(
            'event' => $event)
        );
    }

    public function index()
    {
        $this->set(array(
            'titleForLayout' => 'Website Widgets'
        ));
        $this->layout = 'no_sidebar';
    }

    // Produces a view listing the upcoming events for a given location
    public function venue($venueName = '', $startingDate = null)
    {
        if (!$startingDate) {
            $startingDate = date('Y-m-d');
        }

        $eventResults = $this->Event->find('all', array(
            'conditions' => array(
                'published' => 1,
                'date >=' => $startingDate,
                'location LIKE' => $venueName
            ),
            'fields' => array('id', 'title', 'date', 'time_start', 'time_end', 'cost', 'description'),
            'contain' => false,
            'order' => array('date', 'time_start'),
            'limit' => 1
        ));
        $events = array();
        foreach ($eventResults as $result) {
            $date = $result['Event']['date'];
            $events[$date][] = $result;
        }
        if ($this->request->is('ajax')) {
            $this->layout = 'widgets/ajax';
        } else {
            $this->layout = 'widgets/venue';
        }
        $this->set(array(
            'events' => $events,
            'titleForLayout' => 'Upcoming Events',
            'is_ajax' => $this->request->is('ajax'),
            'startingDate' => $startingDate,
            'venueName' => $venueName
        ));
    }

    public function demoFeed()
    {
        $this->__setDemoData('feed');
        $this->layout = 'ajax';
        $this->render('customize/demo');
    }

    public function demoMonth()
    {
        $this->__setDemoData('month');
        $this->layout = 'ajax';
        $this->render('customize/demo');
    }

    public function customizeFeed()
    {
        $this->__setDemoData('feed');
        $this->set('titleForLayout', 'Customize Feed Widget');
        $this->layout = 'no_sidebar';
        $this->render('customize/feed');
    }

    public function customizeMonth()
    {
        $this->__setDemoData('month');
        $this->set('titleForLayout', 'Customize Month Widget');
        $this->layout = 'no_sidebar';
        $this->render('customize/month');
    }
}
