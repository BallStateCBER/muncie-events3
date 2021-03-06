<?php
namespace App\Controller;

use Cake\Http\Exception\InternalErrorException;
use Cake\Routing\Router;

class WidgetsController extends AppController
{
    public $name = 'Widgets';
    public $uses = ['Event', 'Widget'];
    public $components = [];
    public $helpers = [];

    public $customStyles = [];

    public $widgetType = null;

    /**
     * Initialize hook method.
     *
     * @return void
     * @throws \Exception
     */
    public function initialize()
    {
        parent::initialize();
        // anyone can access widgets
        $this->Auth->allow([
            'customizeFeed', 'customizeMonth', 'day', 'demoFeed', 'demoMonth', 'event', 'feed', 'index', 'month', 'venue'
        ]);
    }

    /**
     * getting default styles
     *
     * @return array
     */
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
                'tags_included' => '',
                'tags_excluded' => ''
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
                $defaults['eventOptions']['eventsDisplayedPerDay'] = 2;
                break;
        }

        return $defaults;
    }

    /**
     * is it a feed or a month widget?
     *
     * @param string $widgetType feed or month
     * @return void
     */
    public function setType($widgetType)
    {
        switch ($widgetType) {
            case 'feed':
            case 'month':
                $this->widgetType = $widgetType;
                break;
            default:
                throw new InternalErrorException('Unknown widget type: ' . $widgetType);
        }
    }

    /**
     * get custom options for widgets
     *
     * @return array
     */
    public function getOptions()
    {
        if (empty(filter_input(INPUT_SERVER, 'QUERY_STRING'))) {
            return [];
        }
        $options = [];
        $parameters = explode('&', urldecode(filter_input(INPUT_SERVER, 'QUERY_STRING')));
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

    /**
     * generates the iframe query string
     *
     * @return string
     */
    public function getIframeQueryString()
    {
        if (empty(filter_input(INPUT_SERVER, 'QUERY_STRING'))) {
            return '';
        }

        $defaults = $this->getDefaults();
        $iframeParams = [];
        $parameters = explode('&', urldecode(filter_input(INPUT_SERVER, 'QUERY_STRING')));
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
                if (!isset($defaults['iframeOptions'][$key])) {
                    $iframeParams[$key] = $val;
                }
            }
        }

        return http_build_query($iframeParams, '', '&');
    }

    /**
     * Returns TRUE if $key is found in default_styles, default_iframeOptions, or default_eventOptions and $val is not the default value
     *
     * @param string $key of style
     * @param string $val of style
     * @return bool
     */
    public function isValidNondefaultOption($key, $val)
    {
        $defaults = $this->getDefaults();
        if (isset($defaults['styles'][$key])) {
            if ($defaults['styles'][$key] != $val) {
                return true;
            }
        } elseif (isset($defaults['eventOptions'][$key])) {
            if ($defaults['eventOptions'][$key] != $val) {
                return true;
            }
        } elseif (isset($defaults['iframeOptions'][$key])) {
            if ($defaults['iframeOptions'][$key] != $val) {
                return true;
            }
        }

        return false;
    }

    /**
     * adding iframe styles
     *
     * @param array $options for iframe styles
     * @return string
     */
    public function getIframeStyles($options)
    {
        $iframeStyles = '';
        $defaults = $this->getDefaults();

        // Dimensions for height
        foreach (['height'] as $dimension) {
            $unit = isset($options[$dimension]) ? (substr($options[$dimension], -1) == '%' ? '%' : 'px') : 'px';
            $value = isset($options[$dimension]) ? (preg_replace("/[^0-9]/", "", $options[$dimension])) : $defaults['iframeOptions'][$dimension];
            $iframeStyles .= "$dimension:{$value}$unit;";
        }
        // Dimensions for width
        foreach (['width'] as $dimension) {
            $unit = isset($options[$dimension]) ? (substr($options[$dimension], -1) == '%' ? '%' : 'px') : '%';
            $value = isset($options[$dimension]) ? (preg_replace("/[^0-9]/", "", $options[$dimension])) : $defaults['iframeOptions'][$dimension];
            $iframeStyles .= "$dimension:{$value}$unit;";
        }

        // Border
        if (isset($options['outerBorder']) && $options['outerBorder'] == 0) {
            $iframeStyles .= "border:0;";
        }
        if (!isset($options['outerBorder']) || !$options['outerBorder'] == 0) {
            $outerBorderColor = isset($options['borderColorDark']) ? $options['borderColorDark'] : $defaults['styles']['borderColorDark'];
            $iframeStyles .= "border:1px solid $outerBorderColor;";
        }

        return $iframeStyles;
    }

    /**
     * adding custom styles
     *
     * @param mixed $elements for custom styles
     * @param mixed $rules for custom styles
     * @return void
     */
    public function addCustomStyle($elements, $rules)
    {
        if (!is_array($elements)) {
            $elements = [$elements];
        }
        if (!is_array($rules)) {
            $rules = [$rules];
        }
        foreach ($elements as $element) {
            foreach ($rules as $rule) {
                $this->customStyles[$element][] = $rule;
            }
        }
    }

    /**
     * setting the options
     *
     * @param array $options which styles are we processing
     * @return void
     */
    public function processCustomStyles($options)
    {
        if (empty($options)) {
            return;
        }
        $defaults = $this->getDefaults();
        foreach ($options as $var => $val) {
            if (mb_stripos($var, 'amp;') !== false) {
                $var = str_replace('amp;', '', $var);
            }
            $val = trim($val);
            $var = trim($var);
            if ($val == '') {
                continue;
            } elseif (isset($defaults['styles'][$var])) {
                if ($defaults['styles'][$var] == $val) {
                    continue;
                }
            } else {
                continue;
            }
            if (method_exists($this, $var . "Style")) {
                $method = $var . "Style";
                $this->$method($val);
            }
        }
    }

    /**
     * setting the default text color color
     *
     * @param string $val is the text color
     * @return void
     */
    private function textColorDefaultStyle($val)
    {
        $this->addCustomStyle(
            'body',
            "color:$val;"
        );
        if ($this->widgetType == 'feed') {
            $this->addCustomStyle(
                '#event_list li a',
                "color:$val;"
            );
        } elseif ($this->widgetType == 'month') {
            $this->addCustomStyle(
                ['table.calendar thead', '#event_lists .time'],
                "color:$val;"
            );
        }
    }

    /**
     * setting the light text color color
     *
     * @param string $val is the text color
     * @return void
     */
    private function textColorLightStyle($val)
    {
        $this->addCustomStyle(
            [
                'div.header',
                'div.header a',
                '.event table.details th',
                '.event .footer',
                '#widget_filters',
                '#event_list li .icon:before'
            ],
            "color:$val;"
        );
        $this->addCustomStyle(
            'ul.header li',
            "border-right:1px solid $val;"
        );
        if ($this->widgetType == 'feed') {
            $this->addCustomStyle(
                [
                    '#event_list h2.day',
                    '#event_list p.no_events',
                    '#load_more_events_wrapper.loading a',
                ],
                "color:$val;"
            );
        }
    }

    /**
     * setting the link colors
     *
     * @param string $val is the link color
     * @return void
     */
    private function textColorLinkStyle($val)
    {
        $this->addCustomStyle(
            'a',
            "color:$val;"
        );
        if ($this->widgetType == 'feed') {
            $this->addCustomStyle(
                '#event_list li a.event_link .title',
                "color:$val;"
            );
        }
    }

    /**
     * setting the light border color color
     *
     * @param string $val is the border color
     * @return void
     */
    private function borderColorLightStyle($val)
    {
        $this->addCustomStyle(
            'a.back:first-child',
            "border-bottom:1px solid $val;"
        );
        $this->addCustomStyle(
            'a.back:last-child',
            "border-top:1px solid $val;"
        );
        $this->addCustomStyle(
            '.event .description',
            "border-top:1px solid $val;"
        );
        $this->addCustomStyle(
            '#widget_filters',
            "border:1px solid $val;"
        );
        if ($this->widgetType == 'feed') {
            $this->addCustomStyle(
                '#event_list li',
                "border-bottom-color:$val;"
            );
            $this->addCustomStyle(
                '#event_list li:first-child',
                "border-color:$val;"
            );
        } elseif ($this->widgetType == 'month') {
            $this->addCustomStyle(
                '#event_lists .close',
                "border-color:$val;"
            );
        }
    }

    /**
     * setting the dark border color color
     *
     * @param string $val is the border color
     * @return void
     */
    private function borderColorDarkStyle($val)
    {
        if ($this->widgetType == 'feed') {
            $this->addCustomStyle(
                '#event_list li:hover',
                "border-color:$val;"
            );
        } elseif ($this->widgetType == 'month') {
            $this->addCustomStyle(
                [
                    'table.calendar td',
                    'table.calendar thead'
                ],
                "border-color:$val;"
            );
        }
    }

    /**
     * setting the default background color
     *
     * @param string $val is the background color
     * @return void
     */
    private function backgroundColorDefaultStyle($val)
    {
        $this->addCustomStyle(
            [
                'html, body',
                '#loading div:nth-child(1)'
            ],
            "background-color:$val;"
        );
        if ($this->widgetType == 'month') {
            $this->addCustomStyle(
                '#event_lists > div > div',
                "background-color:$val;"
            );
        }
    }

    /**
     * setting the background color
     *
     * @param string $val is the background color
     * @return void
     */
    private function backgroundColorAltStyle($val)
    {
        $this->addCustomStyle(
            '#widget_filters',
            "background-color:$val;"
        );
        if ($this->widgetType == 'feed') {
            $this->addCustomStyle(
                '#event_list li',
                "background-color:$val;"
            );
        } elseif ($this->widgetType == 'month') {
            $this->addCustomStyle(
                [
                    'table.calendar tbody li:nth-child(2n)',
                    '#event_lists a.event:nth-child(even)',
                    '#event_lists .close'
                ],
                "background-color:$val;"
            );
        }
    }

    /**
     * how big should the fonts be?
     *
     * @param int $val size of the fonts
     * @return void
     */
    private function fontSizeStyle($val)
    {
        if ($this->widgetType == 'month') {
            $this->addCustomStyle(
                [
                    'table.calendar tbody li',
                    'table.calendar .no_events'
                ],
                "font-size:$val;"
            );
        }
    }

    /**
     * sow all icons
     *
     * @param bool $val whether or not to use this
     * @return void
     */
    private function showIconsStyle($val)
    {
        if ($val) {
            return;
        }
        if ($this->widgetType == 'month') {
            $this->addCustomStyle(
                'table.calendar .icon:before',
                "display:none;"
            );
        }
    }

    /**
     * hide the general events icon
     *
     * @param bool $val whether or not to use this
     * @return void
     */
    private function hideGeneralEventsIconStyle($val)
    {
        if (!$val) {
            return;
        }
        if ($this->widgetType == 'month') {
            $this->addCustomStyle(
                'table.calendar .icon-general-events:before',
                "display:none;"
            );
        }
    }

    /**
     * setting the data for the demo
     *
     * @param string $widgetType month or feed
     * @return void
     */
    public function setDemoDataPr($widgetType)
    {
        $this->setType($widgetType);
        $iframeQueryString = str_replace(['%3D', '%25'], ['=', '%'], $this->getIframeQueryString());
        $options = $this->getOptions();
        $iframeStyles = $this->getIframeStyles($options);
        $codeUrl = Router::url([
            'controller' => 'widgets',
            'action' => $widgetType,
            '?' => $iframeQueryString
        ], true);
        $this->set([
            'defaults' => $this->getDefaults(),
            'iframeStyles' => $iframeStyles,
            'codeUrl' => str_replace('0=', '', urldecode($codeUrl)),
            'categories' => $this->Categories->find('list')->toArray(),
            'options' => $options,
            'iframeQueryString' => $iframeQueryString
        ]);
    }

    /**
     * Produces a view that lists seven event-populated days, starting with $startDate
     *
     * @param string|null $nextStartDate of events
     * @return void
     */
    public function feed($nextStartDate = null)
    {
        $this->setDemoDataPr('feed');

        if ($nextStartDate == null) {
            $nextStartDate = date('Y-m-d');
        }
        $endDate = strtotime($nextStartDate . ' + 2 weeks');
        $events = $this->Events->getStartEndEvents($nextStartDate, $endDate, null);

        $options = $_GET;
        $filters = $this->Events->getValidFilters($options);

        if (!empty($options)) {
            $events = $this->Events->getStartEndEvents($nextStartDate, $endDate, $options);
        }

        $this->indexEvents($events);

        $this->viewBuilder()->setLayout($this->request->is('ajax') ? 'Widgets' . DS . 'ajax' : 'Widgets' . DS . 'feed');
        $this->processCustomStyles($options);

        // filter_input(INPUT_SERVER, 'QUERY_STRING') includes the base url in AJAX requests for some reason
        $baseUrl = Router::url(['controller' => 'widgets', 'action' => 'feed'], true);
        $queryString = str_replace($baseUrl, '', filter_input(INPUT_SERVER, 'QUERY_STRING', FILTER_SANITIZE_STRING));

        $eventIds = [];
        foreach ($events as $event) {
            $eventIds[] = $event->id;
        }

        $this->set([
            'eventIds' => $eventIds,
            'all_events_url' => str_replace(['%3D', '%25', '0='], ['=', '%', ''], $this->getAllEventsUrlPr('feed', $queryString)),
            'titleForLayout' => 'Upcoming Events',
            'isAjax' => $this->request->is('ajax'),
            'filters' => $filters,
            'customStyles' => $this->customStyles
        ]);
    }

    /**
     * Produces a grid-calendar view for the provided month
     *
     * @param string|null $yearMonth 'yyyy-mm', current month by default
     * @return void
     */
    public function month($yearMonth = null)
    {
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
        $prevYear = ($month == 1) ? $year - 1 : $year;
        $prevMonth = ($month == 1) ? 12 : $month - 1;
        $nextYear = ($month == 12) ? $year + 1 : $year;
        $nextMonth = ($month == 12) ? 1 : $month + 1;
        $today = date('Y') . date('m') . date('j');

        $options = $_GET;
        if (!isset($options)) {
            $options = [];
        }
        $filters = $this->Events->getValidFilters($options);

        $events = !empty($options) ? $this->Events->getFilteredEvents($yearMonth, $nextMonth, $options) : $this->Events->getMonthEvents($yearMonth);
        $this->indexEvents($events);

        $this->processCustomStyles($options);

        // filter_input(INPUT_SERVER, 'QUERY_STRING') includes the base url in AJAX requests for some reason
        $baseUrl = Router::url(['controller' => 'widgets', 'action' => 'month'], true);
        $queryString = str_replace($baseUrl, '', filter_input(INPUT_SERVER, 'QUERY_STRING', FILTER_SANITIZE_STRING));

        $eventsForJson = [];
        $randomArray = [];
        $datesForJson = [];

        foreach ($events as $event) {
            $thisMonth = date('m', strtotime($event->date));
            $thisYear = date('Y', strtotime($event->date));
            if ($thisMonth == $month && $thisYear == $year) {
                $date = date('Y-m-d', strtotime($event->date));
                $eventsForJson[$date] = [
                    'heading' => 'Events on ' . date('F j, Y', (strtotime($date))),
                    'events' => []
                ];
                $randomArray[] = [
                    'id' => $event->id,
                    'title' => $event->title,
                    'category_name' => $event->category['name'],
                    'category_icon_class' => 'icon-' . strtolower(str_replace(' ', '-', $event->category['name'])),
                    'url' => Router::url(['controller' => 'Events', 'action' => 'view', 'id' => $event->id]),
                    'date' => $event->date->format('Y-m-d'),
                    'time' => $event->time_start->format('g:ia')
                ];
                $datesForJson[] = $event->date->format('Y-m-d');
            }
        }
        $datesForJson = array_unique($datesForJson);

        foreach ($datesForJson as $date) {
            foreach ($randomArray as $event) {
                if ($event['date'] == $date) {
                    $eventsForJson[$date]['events'][] = $event;
                }
            }
        }

        $this->set([
            'titleForLayout' => "$monthName $year",
            'eventsDisplayedPerDay' => 1,
            'all_events_url' => str_replace(['%3D', '%25', '0='], ['=', '%', ''], $this->getAllEventsUrlPr('month', $queryString)),
            'categories' => $this->Categories->find('list')->toArray(),
            'customStyles' => $this->customStyles
        ]);
        $this->set(compact('month', 'year', 'timestamp', 'preSpacer', 'lastDay', 'prevYear', 'prevMonth', 'nextYear', 'nextMonth', 'today', 'monthName', 'eventsForJson', 'filters', 'options'));
        $this->viewBuilder()->setLayout($this->request->is('ajax') ? 'Widgets' . DS . 'ajax' : 'Widgets' . DS . 'month');
    }

    /**
     * Loads a list of all events on a given day, used by the month widget
     *
     * @param int $year Format: yyyy
     * @param int $month Format: mm
     * @param int $day Format: dd
     *
     * @return void
     */
    public function day($year, $month, $day)
    {
        $month = str_pad($month, 2, '0', STR_PAD_LEFT);
        $day = str_pad($day, 2, '0', STR_PAD_LEFT);
        $options = $_GET;
        $filters = $this->Events->getValidFilters($options);
        $events = $this->Events->getEventsOnDay($year, $month, $day, $filters);
        $this->set([
            'titleForLayout' => 'Events on ' . date('F jS, Y', mktime(0, 0, 0, $month, $day, $year)),
            'events' => $events
        ]);
    }

    /**
     * Accepts a query string and returns the URL to view this calendar with no filters (but custom styles retained)
     *
     * @param string $action month or feed
     * @param string $queryString for all events
     * @return string
     */
    private function getAllEventsUrlPr($action, $queryString)
    {
        $filteredParams = [];
        if (!empty($queryString)) {
            $parameters = explode('&', urldecode($queryString));
            $defaults = $this->getDefaults();
            foreach ($parameters as $paramPair) {
                $pairSplit = explode('=', $paramPair);
                list($var, $val) = $pairSplit;
                if (!isset($defaults['eventOptions'][$var])) {
                    $filteredParams[$var] = $val;
                }
            }
        }
        $newQueryString = (!empty($queryString)) ? http_build_query($filteredParams, '', '&') : '';

        return Router::url([
            'controller' => 'widgets',
            'action' => $action,
            '?' => $newQueryString
        ]);
    }

    /**
     * event page for the widget
     *
     * @param int $id of the event
     * @return null
     */
    public function event($id)
    {
        $event = $this->Events->get($id, [
            'contain' => [
                'Users', 'Categories', 'EventSeries', 'Images', 'Tags'
            ]
        ]);
        if (empty($event)) {
            $this->Flash->error("Sorry, but we couldn't find the event (#$id) you were looking for.");

            return null;
        }
        $this->viewBuilder()->setLayout('Widgets' . DS . 'feed');
        $this->set([
            'event' => $event
        ]);

        return null;
    }

    /**
     * widgets index
     *
     * @return void
     */
    public function index()
    {
        $this->set([
            'titleForLayout' => 'Website Widgets'
        ]);
        $this->viewBuilder()->setLayout('no_sidebar');
    }

    /**
     * Produces a view listing the upcoming events for a given location
     *
     * @param string $venueName of the venue
     * @param string|null $startingDate of the event
     * @return void
     */
    public function venue($venueName = '', $startingDate = null)
    {
        if (!$startingDate) {
            $startingDate = date('Y-m-d');
        }

        // $eventResults initially had a limit 1 so deal w that when you need to?
        $eventResults = $this->Events->find()
            ->where([
                'published' => 1,
                'date >=' => $startingDate,
                'location LIKE' => $venueName
            ])
            ->order(['date' => 'ASC']);
        $events = [];
        foreach ($eventResults as $result) {
            $date = date_format($result->date, 'Y-m-d');
            $events[$date][] = $result;
        }
        if ($this->request->is('ajax')) {
            $this->viewBuilder()->setLayout('widgets/ajax');
        }
        $this->set([
            'events' => $events,
            'eventResults' => $eventResults,
            'titleForLayout' => 'Upcoming Events',
            'is_ajax' => $this->request->is('ajax'),
            'startingDate' => $startingDate,
            'venueName' => $venueName
        ]);
    }

    /**
     * demoFeed method
     *
     * @return void
     */
    public function demoFeed()
    {
        $this->setDemoDataPr('feed');
        $this->viewBuilder()->setLayout('ajax');
        $this->render('customize/demo');
    }

    /**
     * demoMonth method
     *
     * @return void
     */
    public function demoMonth()
    {
        $this->setDemoDataPr('month');
        $this->viewBuilder()->setLayout('ajax');
        $this->render('customize/demo');
    }

    /**
     * customizeFeed method
     *
     * @return void
     */
    public function customizeFeed()
    {
        $this->setDemoDataPr('feed');
        $this->set([
            'titleForLayout' => 'Customize Feed Widget',
            'type' => 'feed'
        ]);
        $this->viewBuilder()->setLayout('no_sidebar');
        $this->render('customize');
    }

    /**
     * customizeMonth method
     *
     * @return void
     */
    public function customizeMonth()
    {
        $this->setDemoDataPr('month');
        $this->set([
            'titleForLayout' => 'Customize Month Widget',
            'type' => 'month'
        ]);
        $this->viewBuilder()->setLayout('no_sidebar');
        $this->render('customize');
    }
}
