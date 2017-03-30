<?php
namespace App\View\Helper;

use Cake\View\Helper;

class CalendarHelper extends Helper {
	public $helpers = ['Html', 'Js'];

	/**
	 * Returns a header describing the dates included in this selection
	 */
	public function selectionHeader($events, $boundary, $starting_date, $ending_date) {
		if (empty($events)) {
			$boundary_date = ($boundary[1] == date('Y-m-d')) ? 'Today' : date('M j, Y', strtotime($boundary[1]));
			return ($boundary[0] == 'start') ? 'After '.$boundary_date : 'Before '.$boundary_date;
		}

		$retval = '';
		$starts_today = $starting_date == date('Y-m-d');
		if ($starting_date == $ending_date) {
			$retval .= $starts_today ? 'Today' : date('M j, Y', strtotime($starting_date));
		} else {
			$sy = date('Y', strtotime($starting_date));
			$ey = date('Y', strtotime($ending_date));
			if ($sy != $ey) {
				$retval .= $starts_today ? 'Today' : date('M j, Y', strtotime($starting_date));
				$retval .= ' to '.date('M j, Y', strtotime($ending_date));
			} else {
				$sm = date('M', strtotime($starting_date));
				$em = date('M', strtotime($ending_date));
				if ($sm != $em) {
					$retval .= $starts_today ? 'Today' : date('M j', strtotime($starting_date));
					$retval .= ' to '.date('M j, Y', strtotime($ending_date));
				} else {
					if ($starts_today) {
						$retval .= 'Today to '.date('M j, Y', strtotime($ending_date));
					} else {
						$retval .= date('M j', strtotime($starting_date));
						$retval .= '-'.date('j, Y', strtotime($ending_date));
					}
				}
			}
		}
		return $retval;
	}

	public function prevLink($starting_date, $filter) {
		if ($starting_date) {
			$prev_url = array_merge(
				[
					'controller' => 'events',
					'action' => 'accordion',
					'end_date' => date('Y-m-d', strtotime("$starting_date - 1 day"))
				],
				$this->__getFilterUrlParams($filter)
			);
			return $this->Js->link(
				'&larr; <span>Previous</span> <img id="event_accordion_prev_indicator" src="/img/loading_small.gif" style="visibility: hidden;" />',
				$prev_url,
				[
					'update' => 'event_accordion_inner',
					'before' => "$('#event_accordion_prev_indicator').css('visibility', 'visible');",
					'escape' => false,
					'evalScripts' => true,
					'class' => 'prev'
				]
			);
		} else {
			// Non-breaking space forces element to appear even if it has no link
			return '&nbsp;';
		}
	}

	public function nextLink($ending_date, $filter) {
		if ($ending_date) {
			$next_url = array_merge(
				[
					'controller' => 'events',
					'action' => 'accordion',
					'start_date' => date('Y-m-d', strtotime("$ending_date + 1 day"))
				],
				$this->__getFilterUrlParams($filter)
			);
			return $this->Js->link(
				'<img id="event_accordion_next_indicator" src="/img/loading_small.gif" style="visibility: hidden;" /> <span>Next</span> &rarr;',
				$next_url,
				[
					'update' => 'event_accordion_inner',
					'before' => "$('event_accordion_next_indicator').setStyle({visibility: 'visible'});",
					'escape' => false,
					'evalScripts' => true,
					'class' => 'next'
				]
			);
		} else {
			// Non-breaking space forces element to appear even if it has no link
			return '&nbsp;';
		}
	}

	/**
	 * Returns subheader reflecting current tag filter(s)
	 */
	public function tagFilterHeader($filter) {
		$retval = '';
		if (isset($filter['tag'])) {
			$retval .= '<br /><span class="filter">Tag: '.ucwords($filter['tag']['name']).'</span>';
			$unselect_tag_url = ['controller' => 'events', 'action' => 'accordion'];
			if (isset($filter['categories'])) {
				$unselect_tag_url['categories'] = implode(',', $filter['categories']);
			}
			$retval .= $this->Js->link(
				'Unselect tag',
				$unselect_tag_url,
				[
					'update' => 'event_accordion_inner',
					'before' => "$('#event_accordion_loading_indicator').show();",
					'escape' => false,
					'evalScripts' => true,
					'class' => 'reset'
				]
			);
		}
		return $retval;
	}

	/**
	 * Return filter parameters to be used in 'previous' and 'next' links
	 * @param Array $filter
	 * @return Array
	 */
	private function __getFilterUrlParams($filter) {
		$filter_url_params = [];
		if (isset($filter['tag'])) {
			$filter_url_params['tag'] = $filter['tag']['id'].'_'.Inflector::slug($filter['tag']['name']);
		}
		return $filter_url_params;
	}

	/**
	 * Assuming no events were found, outputs an appropriate message depending on $filter
	 * @param Array $filter
	 * @return string
	 */
	public function noEventsMessage($filter) {
		if (isset($filter['tag'])) {
			return 'The calendar contains no events with that tag in this date range.';
		} elseif (isset($filter['categories'])) {
			$that_category = count($filter['categories']) == 1 ? 'that category' : 'those categories';
			return "The calendar contains no events in $that_category in this date range.";
		} else {
			return 'The calendar contains no events in this date range.';
		}
	}

	/**
	 * Returns an <hgroup> tag for the provided Y-m-d format date string
	 * @param string $date
	 * @return string
	 */
	public function dayHeaders($date) {
		if ($date == date('Y-m-d')) {
			$day = 'Today';
			$this_week = true;
		} elseif ($date == date('Y-m-d', strtotime('tomorrow'))) {
			$day = 'Tomorrow';
			$this_week = true;
		} else {
			$day = date('l', strtotime($date));
			$this_week = ($date > date('Y-m-d') && $date < date('Y-m-d', strtotime('today + 6 days')));
			if ($this_week) {
				$day = 'This '.$day;
			}
		}
		$timestamp = strtotime($date);

		$pattern = 'M j, Y';
		$header_short_date = '<h2 class="short_date">'.date($pattern, $timestamp).'</h2>';
		$header_day = '<h2 class="day">'.$day.'</h2>';
		$headers = $header_short_date.$header_day;
		$classes = 'event_accordion';
		if ($this_week) {
			$classes .= ' this_week';
		}
		return "<hgroup class=\"$classes\">$headers</hgroup>";
	}

	public function eventTime($event) {
		$start_stamp = strtotime($event['Event']['time_start']);
		if (substr($event['Event']['time_start'], 3, 5) == '00:00') {
			$retval = date('ga', $start_stamp);
		} else {
			$retval = date('g:ia', $start_stamp);
		}
		if ($event['Event']['time_end']) {
			$end_stamp = strtotime($event['Event']['time_end']);
			if (substr($event['Event']['time_end'], 3, 5) == '00:00') {
				$retval .= ' to '.date('ga', $end_stamp);
			} else {
				$retval .= ' to '.date('g:ia', $end_stamp);
			}
		}
		return $retval;
	}

	/**
	 * Returns a linked list of tags
	 * @param Array $event
	 * @return string
	 */
	public function eventTags($event) {
		$linked_tags = [];
		foreach ($event['Tag'] as $tag) {
			$tag_link_id = "filter_tag_inline_{$event['Event']['id']}_{$tag['id']}";
			$tag_slug = "{$tag['id']}_".Inflector::slug($tag['name']);
			$linked_tags[] = $this->Html->link($tag['name'],
				[
					'controller' => 'events',
					'action' => 'tag',
					'slug' => $tag_slug
				],
				[
					'escape' => false,
					'id' => $tag_link_id
				]
			);
		}
		return implode(', ', $linked_tags);
	}

	/**
	 * Returns a formatted version of the date of the provided event
	 * @param array $event
	 * @return string
	 */
	public function date($event) {
		$date_split = explode('-', $event['Event']['date']);
		$year = $date_split[0];
		$month = ($date_split[1] < 10) ? substr($date_split[1], 1, 1) : $date_split[1];
		$day = ($date_split[2] < 10) ? substr($date_split[2], 1, 1) : $date_split[2];
		return date('l, F j, Y', mktime(0, 0, 0, $month, $day, $year));
	}

	/**
	 * Returns a formatted version of the time of the provided event
	 * @param array $event
	 * @return string
	 */
	public function time($event) {
		$retval = date('g:ia', strtotime($event['Event']['time_start']));
		if ($event['Event']['time_end']) {
			$retval .= ' to '.date('g:ia', strtotime($event['Event']['time_end']));
		}
		return $retval;
	}

	/**
	 * Returns a linked arrow to the previous day
	 * @param int $timestamp Of the previous day
	 */
	public function prevDay($timestamp) {
		return $this->Html->link(
			'&larr; Previous Day',
		    [
		    	'controller' => 'events',
				'action' => 'day',
				date('m', $timestamp),
				date('d', $timestamp),
				date('Y', $timestamp)
		    ],
			['escape' => false]
		);
	}

	/**
	 * Returns a linked arrow to the next day
	 * @param int $timestamp Of the next day
	 */
	public function nextDay($timestamp) {
		return $this->Html->link(
			'Next Day &rarr;',
		    [
		    	'controller' => 'events',
				'action' => 'day',
				date('m', $timestamp),
				date('d', $timestamp),
				date('Y', $timestamp)
		    ],
			['escape' => false]
		);
	}

	/**
	 * Outputs either a thumbnail (square) image or a small (width-limited) image
	 * @param string $type 'small' or 'tiny'
	 * @param array $params
	 * @return string
	 */
	public function thumbnail($type, $params) {
		if (! isset($params['filename'])) {
			return '';
		}
		$filename = $params['filename'];
		$reduced_path = WWW_ROOT.'img'.DS.'events'.DS.$type.DS.$filename;
		if (! file_exists($reduced_path)) {
			return '';
		}
		$full_path = WWW_ROOT.'img'.DS.'events'.DS.'full'.DS.$filename;
		$class = "thumbnail tn_$type";
		if (isset($params['class'])) {
			$class .= ' '.$params['class'];
		}

		// Reduced image
		$image = '<img src="/img/events/'.$type.'/'.$filename.'" class="'.$class.'" />';


		if (! file_exists($full_path)) {
			return $image;
		}

		// Link to full image
		$rel = "popup";
		if (isset($params['group'])) {
			$rel .= '['.$params['group'].']';
		}
		$link = '<a href="/img/events/full/'.$filename.'" rel="'.$rel.'" class="'.$class.'" alt="image"';
		if (isset($params['caption']) && ! empty($params['caption'])) {
			$link .= ' title="'.$params['caption'].'"';
		}
		$link .= '>'.$image.'</a>';
		return $link;
	}
}
