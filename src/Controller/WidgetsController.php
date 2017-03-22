<?php
namespace app\Controller;

class WidgetsController extends AppController {
	public $name = 'Widgets';
	public $uses = array('Event', 'Widget');
	public $components = array();
	public $helpers = array();

	public $custom_styles = array();

	public function beforeFilter(Event $event) {
		parent::beforeFilter($event);
		$this->Auth->allow();
	}

	public function beforeRender(Event $event) {
		parent::beforeRender($event);
	}

	private function __setDemoData($widget_type) {
		$this->Widget->setType($widget_type);
		$iframe_query_string = $this->Widget->getIframeQueryString();
		$options = $this->Widget->getOptions();
		$iframe_styles = $this->Widget->getIframeStyles($options);
		$this->set(array(
			'defaults' => $this->Widget->getDefaults(),
			'iframe_styles' => $iframe_styles,
			'iframe_url' => Router::url(array(
				'controller' => 'widgets',
				'action' => $widget_type,
				'?' => $iframe_query_string
			), true),
			'code_url' => Router::url(array(
				'controller' => 'widgets',
				'action' => $widget_type,
				'?' => str_replace('&', '&amp;', $iframe_query_string)
			), true),
			'categories' => $this->Event->Category->getAll()
		));
	}

	/**
	 * Produces a view that lists seven event-populated days, starting with $start_date
	 * @param string $start_date 'yyyy-mm-dd', today by default
	 */
	public function feed($start_date = null) {
		$this->__setDemoData('feed');

		// Get relevant event filters
		$options = $_GET;
		$filters = $this->Event->getValidFilters($options);
		$events = $this->Event->getWidgetPage($start_date, $filters);
		$event_ids = array();
		foreach ($events as $date => $days_events) {
			foreach ($days_events as $event) {
				$event_ids[] = $event['Event']['id'];
			}
		}
		$this->layout = $this->request->is('ajax') ? 'Widgets'.DS.'ajax' : 'Widgets'.DS.'feed';
		$this->Widget->processCustomStyles($options);

		// $_SERVER['QUERY_STRING'] includes the base url in AJAX requests for some reason
		$base_url = Router::url(array('controller' => 'widgets', 'action' => 'feed'), true);
		$query_string = str_replace($base_url, '', $_SERVER['QUERY_STRING']);

		$this->set(array(
			'titleForLayout' => 'Upcoming Events',
			'events' => $events,
			'event_ids' => $event_ids,
			'is_ajax' => $this->request->is('ajax'),
			'next_start_date' => $this->Event->getNextStartDate($events),
			'custom_styles' => $this->Widget->custom_styles,
			'filters' => $filters,
			'categories' => $this->Event->Category->getList(),
			'all_events_url' => $this->__getAllEventsUrl('feed', $query_string)
		));
	}

	/**
	 * Produces a grid-calendar view for the provided month
	 * @param string $month 'yyyy-mm', current month by default
	 */
	public function month($year_month = null) {
		$this->__setDemoData('month');

		// Process various date information
		if (! $year_month) {
			$year_month = date('Y-m');
		}
		$split = explode('-', $year_month);
		$year = reset($split);
		$month = end($split);
		$timestamp = mktime(0, 0, 0, $month, 1, $year);
		$month_name = date('F', $timestamp);
		$pre_spacer = date('w', $timestamp);
		$last_day = date('t', $timestamp);
		$post_spacer = 6 - date('w', mktime(0, 0, 0, $month, $last_day, $year));
		$prev_year = ($month == 1) ? $year - 1 : $year;
		$prev_month = ($month == 1) ? 12 : $month - 1;
		$next_year = ($month == 12) ? $year + 1 : $year;
		$next_month = ($month == 12) ? 1 : $month + 1;
		$today = date('Y').date('m').date('j');

		// Get relevant event filters
		$options = $_GET;
		$filters = $this->Event->getValidFilters($options);
		$events = $this->Event->getMonth($year_month, $filters);
		$events_for_json = array();
		foreach ($events as $date => &$days_events) {
			if (! isset($events_for_json[$date])) {
				$events_for_json[$date] = array(
					'heading' => 'Events on '.date('F j, Y', strtotime($date)),
					'events' => array()
				);
			}
			foreach ($days_events as &$event) {
				$time_split = explode(':', $event['Event']['time_start']);
				$timestamp = mktime($time_split[0], $time_split[1]);
				$displayed_time = date('g:ia', $timestamp);
				$event['Event']['displayed_time'] = $displayed_time;
				$events_for_json[$date]['events'][] = array(
					'id' => $event['Event']['id'],
					'title' => $event['Event']['title'],
					'category_name' => $event['Category']['name'],
					'category_icon_class' => 'icon-'.strtolower(str_replace(' ', '-', $event['Category']['name'])),
					'url' => Router::url(array('controller' => 'events', 'action' => 'view', 'id' => $event['Event']['id'])),
					'time' => $displayed_time
				);
			}
		}
		$this->layout = $this->request->is('ajax') ? 'Widgets'.DS.'ajax' : 'Widgets'.DS.'month';
		$this->Widget->processCustomStyles($options);

		// Events displayed per day
		if (isset($options['events_displayed_per_day'])) {
			$events_displayed_per_day = $options['events_displayed_per_day'];
		} else {
			$defaults = $this->Widget->getDefaults();
			$events_displayed_per_day = $defaults['event_options']['events_displayed_per_day'];
		}

		// $_SERVER['QUERY_STRING'] includes the base url in AJAX requests for some reason
		$base_url = Router::url(array('controller' => 'widgets', 'action' => 'month'), true);
		$query_string = str_replace($base_url, '', $_SERVER['QUERY_STRING']);

		$this->set(array(
			'titleForLayout' => "$month_name $year",
			'events_displayed_per_day' => $events_displayed_per_day,
			'custom_styles' => $this->Widget->custom_styles,
			'all_events_url' => $this->__getAllEventsUrl('month', $query_string),
			'categories' => $this->Event->Category->getList()
		));
		$this->set(compact(
			'month', 'year', 'timestamp', 'month_name', 'pre_spacer', 'last_day', 'post_spacer',
			'prev_year', 'prev_month', 'next_year', 'next_month', 'today', 'events',
			'events_for_json', 'filters'
		));
	}

	/**
	 * Loads a list of all events on a given day, used by the month widget
	 * @param int $year Format: yyyy
	 * @param int $month Format: mm
	 * @param int $day Format: dd
	 */
	public function day($year, $month, $day) {
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
	 * @param string $query_string
	 * @return string
	 */
	private function __getAllEventsUrl($action, $query_string) {
		if (empty($query_string)) {
			$new_query_string = '';
		} else {
			$parameters = explode('&', urldecode($query_string));
			$filtered_params = array();
			$defaults = $this->Widget->getDefaults();
			foreach ($parameters as $param_pair) {
				$pair_split = explode('=', $param_pair);
				list($var, $val) = $pair_split;
				if (! isset($defaults['event_options'][$var])) {
					$filtered_params[$var] = $val;
				}
			}
			$new_query_string = http_build_query($filtered_params, '', '&amp;');
		}
		return Router::url(array(
			'controller' => 'widgets',
			'action' => $action,
			'?' => $new_query_string
		));
	}

	public function event($id) {
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

	public function index() {
		$this->set(array(
			'titleForLayout' => 'Website Widgets'
		));
		$this->layout = 'no_sidebar';
	}

	// Produces a view listing the upcoming events for a given location
	public function venue($venue_name = '', $starting_date = null) {
		if (! $starting_date) {
			$starting_date = date('Y-m-d');
		}

		$event_results = $this->Event->find('all', array(
			'conditions' => array(
				'published' => 1,
				'date >=' => $starting_date,
				'location LIKE' => $venue_name
			),
			'fields' => array('id', 'title', 'date', 'time_start', 'time_end', 'cost', 'description'),
			'contain' => false,
			'order' => array('date', 'time_start'),
			'limit' => 1
		));
		$events = array();
		foreach ($event_results as $result) {
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
			'starting_date' => $starting_date,
			'venue_name' => $venue_name
		));
	}

	public function demo_feed() {
		$this->__setDemoData('feed');
		$this->layout = 'ajax';
		$this->render('customize/demo');
	}

	public function demo_month() {
		$this->__setDemoData('month');
		$this->layout = 'ajax';
		$this->render('customize/demo');
	}

	public function customize_feed() {
		$this->__setDemoData('feed');
		$this->set('titleForLayout', 'Customize Feed Widget');
		$this->layout = 'no_sidebar';
		$this->render('customize/feed');
	}

	public function customize_month() {
		$this->__setDemoData('month');
		$this->set('titleForLayout', 'Customize Month Widget');
		$this->layout = 'no_sidebar';
		$this->render('customize/month');
	}
}
