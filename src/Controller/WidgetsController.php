<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Routing\Router;

class WidgetsController extends AppController
{
    public $name = 'Widgets';
    public $uses = ['Event', 'Widget'];
    public $components = [];
    public $helpers = [];

    public $customStyles = [];

    public $widgetType = null;

    public function initialize()
    {
        parent::initialize();
        // anyone can access widgets
        $this->Auth->allow([
            'day', 'event', 'feed', 'index', 'month'
        ]);
    }

    public function setType($widgetType)
    {
        switch ($widgetType) {
            case 'feed':
            case 'month':
                $this->widgetType = $widgetType;
                break;
            default:
                throw new InternalErrorException('Unknown widget type: '.$widgetType);
        }
    }

    public function getOptions()
    {
        if (empty(filter_input(INPUT_SERVER, 'QUERY_STRING', FILTER_SANITIZE_STRING))) {
            return [];
        }
        $options = [];
        $parameters = explode('&', urldecode(filter_input(INPUT_SERVER, 'QUERY_STRING', FILTER_SANITIZE_STRING)));
        foreach ($parameters as $option) {
            $optionSplit = explode('=', $option);
            if (count($optionSplit) != 2) {
                continue;
            }
            list($key, $val) = $optionSplit;

            // Clean up option and skip blanks
            $val = trim($val);
            if ($val == '') {
                continue;
            }
            $key = str_replace('amp;', '', $key);

            // Retain only valid options that differ from their default values
            if ($this->isValidNondefaultOption($key, $val)) {
                $options[$key] = $val;
            }
        }
        return $options;
    }

    public function getDefaults()
    {
        if (!$this->widgetType) {
            throw new InternalErrorException('Widget type is null.');
        }
        $defaults = [
            'styles' => [
                'textColorDefault' => '#000000',
                'textColorLight' => '#666666',
                'textColorLink' => '#0b54a6',
                'borderColorLight' => '#aaaaaa',
                'borderColorDark' => '#000000',
                'backgroundColorDefault' => '#ffffff',
                'backgroundColorAlt' => '#f0f0f0',
                'showIcons' => 1,
                'hideGeneralEventsIcon' => 0
            ],
            'iframeOptions' => [
                'outerBorder' => 1
            ],
            'eventOptions' => [
                'category' => '',
                'location' => '',
                'tagsIncluded' => '',
                'tagsExcluded' => ''
            ]
        ];
        switch ($this->widgetType) {
            case 'feed':
                $defaults['iframeOptions']['height'] = 300;
                $defaults['iframeOptions']['width'] = 100;
                break;
            case 'month':
                $defaults['styles']['fontSize'] = '11px';
                $defaults['styles']['showIcons'] = true;
                $defaults['iframeOptions']['height'] = 400;
                $defaults['iframeOptions']['width'] = 100;
                $defaults['eventOptions']['eventsPerDay'] = 2;
                break;
        }
        return $defaults;
    }

    public function getIframeQueryString()
    {
        if (empty(filter_input(INPUT_SERVER, 'QUERY_STRING', FILTER_SANITIZE_STRING))) {
            return '';
        }
        $defaults = $this->getDefaults();
        $iframeParams = [];
        $parameters = explode('&', urldecode(filter_input(INPUT_SERVER, 'QUERY_STRING', FILTER_SANITIZE_STRING)));
        foreach ($parameters as $option) {
            $optionSplit = explode('=', $option);
            if (count($optionSplit) != 2) {
                continue;
            }
            list($key, $val) = $optionSplit;

            // Clean up option and skip blanks
            $val = trim($val);
            if ($val == '') {
                continue;
            }
            $key = str_replace('amp;', '', $key);

            // Retain only valid params that differ from their default values
            if ($this->isValidNondefaultOption($key, $val)) {
                // Iframe options (applying to the iframe element, but not
                // its contents) aren't included in the query string
                if (!isset($defaults['iframe_options'][$key])) {
                    $iframeParams[$key] = $val;
                }
            }
        }
        return http_build_query($iframeParams, '', '&amp;');
    }

    public function getIframeStyles($options)
    {
        $iframeStyles = '';
        $defaults = $this->getDefaults();

        // Dimensions for height
        foreach (['height'] as $dimension) {
            if (isset($options[$dimension])) {
                $unit = substr($options[$dimension], -1) == '%' ? '%' : 'px';
                $value = preg_replace("/[^0-9]/", "", $options[$dimension]);
            }
            if (!isset($options[$dimension])) {
                $unit = 'px';
                $value = $defaults['iframeOptions'][$dimension];
            }
            $iframeStyles .= "$dimension:{$value}$unit;";
        }
        // Dimensions for width
        foreach (['width'] as $dimension) {
            if (isset($options[$dimension])) {
                $unit = substr($options[$dimension], -1) == '%' ? '%' : 'px';
                $value = preg_replace("/[^0-9]/", "", $options[$dimension]);
            }
            if (!isset($options[$dimension])) {
                $unit = '%';
                $value = $defaults['iframeOptions'][$dimension];
            }
            $iframeStyles .= "$dimension:{$value}$unit;";
        }

        // Border
        if (isset($options['outerBorder']) && $options['outerBorder'] == 0) {
            $iframeStyles .= "border:0;";
        } else {
            if (isset($options['borderColorDark'])) {
                $outerBorderColor = $options['borderColorDark'];
            }
            if (!isset($options['borderColorDark'])) {
                $outerBorderColor = $defaults['styles']['borderColorDark'];
            }
            $iframeStyles .= "border:1px solid $outerBorderColor;";
        }

        return $iframeStyles;
    }

    private function setDemoDataPr($widgetType)
    {
        $this->setType($widgetType);
        $iframeQueryString = $this->getIframeQueryString();
        $options = $this->getOptions();
        $iframeStyles = $this->getIframeStyles($options);
        $this->set([
            'defaults' => $this->getDefaults(),
            'iframeStyles' => $iframeStyles,
            'iframeUrl' => Router::url([
                'controller' => 'widgets',
                'action' => $widgetType,
                '?' => $iframeQueryString
            ], true),
            'code_url' => Router::url([
                'controller' => 'widgets',
                'action' => $widgetType,
                '?' => str_replace('&', '&amp;', $iframeQueryString)
            ], true),
            'categories' => $this->Categories
        ]);
    }

    /**
     * Produces a view that lists seven event-populated days, starting with $startDate
     * @param string $startDate 'yyyy-mm-dd', today by default
     */
    public function feed($startDate = null)
    {
        $this->loadModel('Events');
        $this->setDemoDataPr('feed');

        $events = $this->Events
            ->find('all', [
            'contain' => ['Users', 'Categories', 'EventSeries', 'Images', 'Tags'],
            'order' => ['date' => 'ASC']
            ])
            ->where(['date >=' => date('Y-m-d')])
            ->toArray();
        $this->indexEvents($events);

        $this->viewBuilder()->layout($this->request->is('ajax') ? 'Widgets'.DS.'ajax' : 'Widgets'.DS.'feed');

        // $_SERVER['QUERY_STRING'] includes the base url in AJAX requests for some reason
        $baseUrl = Router::url(['controller' => 'widgets', 'action' => 'feed'], true);
        $queryString = str_replace($baseUrl, '', filter_input(INPUT_SERVER, 'QUERY_STRING', FILTER_SANITIZE_STRING));

        $this->set([
            'titleForLayout' => 'Upcoming Events',
            'isAjax' => $this->request->is('ajax')
        ]);
    }

    /**
     * Produces a grid-calendar view for the provided month
     * @param string $month 'yyyy-mm', current month by default
     */
    public function month($yearMonth = null)
    {
        $this->loadModel('Events');
        $this->setDemoDataPr('month');

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

        $events = $this->Events
            ->find('all', [
            'contain' => ['Users', 'Categories', 'EventSeries', 'Images', 'Tags'],
            'order' => ['date' => 'ASC']
            ])
            ->where(['date >=' => date('Y-m-d')])
            ->toArray();
        $this->indexEvents($events);

        $this->viewBuilder()->layout($this->request->is('ajax') ? 'Widgets'.DS.'ajax' : 'Widgets'.DS.'month');

        // $_SERVER['QUERY_STRING'] includes the base url in AJAX requests for some reason
        $baseUrl = Router::url(['controller' => 'widgets', 'action' => 'month'], true);
        $queryString = str_replace($baseUrl, '', filter_input(INPUT_SERVER, 'QUERY_STRING', FILTER_SANITIZE_STRING));

        $eventsForJson = [];
        foreach ($events as $date => &$days_events) {
            if (! isset($eventsForJson[$date])) {
                $eventsForJson[$date] = [
                    'heading' => 'Events on '.date('F j, Y', strtotime($date)),
                    'events' => []
                ];
            }
            foreach ($days_events as &$event) {
                $timeSplit = explode(':', $event->time_start);
                $timestamp = mktime($timeSplit[0], $timeSplit[1]);
                $displayedTime = date('g:ia', $timestamp);
                $event->displayed_time = $displayedTime;
                $eventsForJson[$date]['events'][] = [
                    'id' => $event->id,
                    'title' => $event->title,
                    'category_name' => $event->category->name,
                    'category_icon_class' => 'icon-'.strtolower(str_replace(' ', '-', $event->category->name)),
                    'url' => Router::url(['controller' => 'events', 'action' => 'view', 'id' => $event->id]),
                    'time' => $displayedTime
                ];
            }

            $this->set([
            'titleForLayout' => "$monthName $year",
            'eventsDisplayedPerDay' => 1,
            'allEventsUrl' => $this->getAllEventsUrlPr('month', $queryString),
            'categories' => $this->Events->Categories->getAll()
        ]);
            $this->set(compact(
            'month', 'year', 'timestamp', 'preSpacer', 'lastDay', 'postSpacer',
            'prevYear', 'prevMonth', 'nextYear', 'nextMonth', 'today',
            'eventsForJson', 'monthName'
        ));
        }
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
        $options = filter_input_array(INPUT_GET);
        $filters = $this->Events->getValidFilters($options);
        $events = $this->Events->getFilteredEventsOnDates("$year-$month-$day", $filters, true);
        $this->set([
            'titleForLayout' => 'Events on '.date('F jS, Y', mktime(0, 0, 0, $month, $day, $year)),
            'events' => $events
        ]);
    }

    /**
     * Accepts a query string and returns the URL to view this calendar with no filters (but custom styles retained)
     * @param string $queryString
     * @return string
     */
    private function getAllEventsUrlPr($action, $queryString)
    {
        if (empty($queryString)) {
            $newQueryString = '';
        } else {
            $parameters = explode('&', urldecode($queryString));
            $filteredParams = [];
            $defaults = $this->Widget->getDefaults();
            foreach ($parameters as $paramPair) {
                $pairSplit = explode('=', $paramPair);
                list($var, $val) = $pairSplit;
                if (!isset($defaults['event_options'][$var])) {
                    $filteredParams[$var] = $val;
                }
            }
            $newQueryString = http_build_query($filteredParams, '', '&amp;');
        }
        return Router::url([
            'controller' => 'widgets',
            'action' => $action,
            '?' => $newQueryString
        ]);
    }

    public function event($id)
    {
        $this->loadModel('Events');
        $event = $this->Events->get($id, [
          'contain' => ['Users', 'Categories', 'EventSeries', 'Images', 'Tags']
      ]);
        if (empty($event)) {
            return $this->renderMessage([
              'title' => 'Event Not Found',
              'message' => "Sorry, but we couldn't find the event (#$id) you were looking for.",
              'class' => 'error'
          ]);
        }
        $this->viewBuilder()->layout('Widgets'.DS.'feed');
        $this->set([
          'event' => $event
          ]
      );
    }

    public function index()
    {
        $this->set([
            'titleForLayout' => 'Website Widgets'
        ]);
        $this->viewBuilder()->layout('no_sidebar');
    }

    // Produces a view listing the upcoming events for a given location
    public function venue($venueName = '', $startingDate = null)
    {
        if (!$startingDate) {
            $startingDate = date('Y-m-d');
        }

        $eventResults = $this->Events->find('all', [
            'conditions' => [
                'published' => 1,
                'date >=' => $startingDate,
                'location LIKE' => $venueName
            ],
            'fields' => ['id', 'title', 'date', 'time_start', 'time_end', 'cost', 'description'],
            'contain' => false,
            'order' => ['date', 'time_start'],
            'limit' => 1
        ]);
        $events = [];
        foreach ($eventResults as $result) {
            $date = $result['Event']['date'];
            $events[$date][] = $result;
        }
        if ($this->request->is('ajax')) {
            $this->viewBuilder()->layout('widgets/ajax');
        } else {
            $this->viewBuilder()->layout('widgets/venue');
        }
        $this->set([
            'events' => $events,
            'titleForLayout' => 'Upcoming Events',
            'is_ajax' => $this->request->is('ajax'),
            'startingDate' => $startingDate,
            'venueName' => $venueName
        ]);
    }

    public function demoFeed()
    {
        $this->setDemoDataPr('feed');
        $this->viewBuilder()->layout('ajax');
        $this->render('customize/demo');
    }

    public function demoMonth()
    {
        $this->setDemoDataPr('month');
        $this->viewBuilder()->layout('ajax');
        $this->render('customize/demo');
    }

    public function customizeFeed()
    {
        $this->setDemoDataPr('feed');
        $this->set('titleForLayout', 'Customize Feed Widget');
        $this->viewBuilder()->layout('no_sidebar');
        $this->render('customize/feed');
    }

    public function customizeMonth()
    {
        $this->setDemoDataPr('month');
        $this->set('titleForLayout', 'Customize Month Widget');
        $this->viewBuilder()->layout('no_sidebar');
        $this->render('customize/month');
    }
}
