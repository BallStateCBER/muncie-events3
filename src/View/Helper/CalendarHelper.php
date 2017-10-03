<?php
namespace App\View\Helper;

use Cake\Utility\Inflector;
use Cake\View\Helper;

class CalendarHelper extends Helper
{
    public $helpers = ['Html', 'Js'];

    /**
     * Returns a header describing the dates included in this selection
     */
/*    public function selectionHeader($events, $boundary, $startingDate, $endingDate)
    {
        if (empty($events)) {
            $boundaryDate = ($boundary[1] == date('Y-m-d')) ? 'Today' : date('M j, Y', strtotime($boundary[1]));
            return ($boundary[0] == 'start') ? 'After '.$boundaryDate : 'Before '.$boundaryDate;
        }

        $retval = '';
        $startsToday = $startingDate == date('Y-m-d');
        if ($startingDate == $endingDate) {
            $retval .= $startsToday ? 'Today' : date('M j, Y', strtotime($startingDate));
        }
        if ($startingDate !== $endingDate) {
            $startYear = date('Y', strtotime($startingDate));
            $endYear = date('Y', strtotime($endingDate));
            if ($startYear != $endYear) {
                $retval .= $startsToday ? 'Today' : date('M j, Y', strtotime($startingDate));
                $retval .= ' to '.date('M j, Y', strtotime($endingDate));
            }
            if ($startYear == $endYear) {
                $startMonth = date('M', strtotime($startingDate));
                $endMonth = date('M', strtotime($endingDate));
                if ($startMonth != $endMonth) {
                    $retval .= $startsToday ? 'Today' : date('M j', strtotime($startingDate));
                    $retval .= ' to '.date('M j, Y', strtotime($endingDate));
                }
                if ($startMonth == $endMonth) {
                    if ($startsToday) {
                        $retval .= 'Today to '.date('M j, Y', strtotime($endingDate));
                    }
                    if (!$startsToday) {
                        $retval .= date('M j', strtotime($startingDate));
                        $retval .= '-'.date('j, Y', strtotime($endingDate));
                    }
                }
            }
        }
        return $retval;
    }

    public function prevLink($startingDate, $filter)
    {
        if ($startingDate) {
            $prevUrl = array_merge(
                [
                    'controller' => 'events',
                    'action' => 'accordion',
                    'end_date' => date('Y-m-d', strtotime("$startingDate - 1 day"))
                ],
                $this->getFilterUrlParamsPr($filter)
            );
            return $this->Js->link(
                '&larr; <span>Previous</span> <img id="event_accordion_prev_indicator" src="/img/loading_small.gif" style="visibility: hidden;" />',
                $prevUrl,
                [
                    'update' => 'event_accordion_inner',
                    'before' => "$('#event_accordion_prev_indicator').css('visibility', 'visible');",
                    'escape' => false,
                    'evalScripts' => true,
                    'class' => 'prev'
                ]
            );
        }
        if (!$startingDate) {
            // Non-breaking space forces element to appear even if it has no link
            return '&nbsp;';
        }
    }

    public function nextLink($endingDate, $filter)
    {
        if ($endingDate) {
            $nextUrl = array_merge(
                [
                    'controller' => 'events',
                    'action' => 'accordion',
                    'startDate' => date('Y-m-d', strtotime("$endingDate + 1 day"))
                ],
                $this->getFilterUrlParamsPr($filter)
            );
            return $this->Js->link(
                '<img id="event_accordion_next_indicator" src="/img/loading_small.gif" style="visibility: hidden;" /> <span>Next</span> &rarr;',
                $nextUrl,
                [
                    'update' => 'event_accordion_inner',
                    'before' => "$('event_accordion_next_indicator').setStyle({visibility: 'visible'});",
                    'escape' => false,
                    'evalScripts' => true,
                    'class' => 'next'
                ]
            );
        }
        if (!$endingDate) {
            // Non-breaking space forces element to appear even if it has no link
            return '&nbsp;';
        }
    }

    /**
     * Returns subheader reflecting current tag filter(s)

    public function tagFilterHeader($filter)
    {
        $retval = '';
        if (isset($filter['tag'])) {
            $retval .= '<br /><span class="filter">Tag: '.ucwords($filter['tag']['name']).'</span>';
            $unselectTagUrl = ['controller' => 'events', 'action' => 'accordion'];
            if (isset($filter['categories'])) {
                $unselectTagUrl['categories'] = implode(',', $filter['categories']);
            }
            $retval .= $this->Js->link(
                'Unselect tag',
                $unselectTagUrl,
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
    }   */

    /**
     * Return filter parameters to be used in 'previous' and 'next' links
     *
     * @param array $filter for params
     * @return array
     */
    private function getFilterUrlParamsPr($filter)
    {
        $filterUrlParams = [];
        if (isset($filter['tag'])) {
            $filterUrlParams['tag'] = $filter['tag']['id'] . '_' . Inflector::slug($filter['tag']['name']);
        }

        return $filterUrlParams;
    }

    /**
     * Returns an <hgroup> tag for the provided Y-m-d format date string
     *
     * @param string $date to be turned into a header
     * @return string
     */
    public function dayHeaders($date)
    {
        if ($date == date('Y-m-d')) {
            $day = 'Today';
            $thisWeek = true;
        } elseif ($date == date('Y-m-d', strtotime('+1 day'))) {
            $day = 'Tomorrow';
            $thisWeek = true;
        } elseif ($date != date('Y-m-d')) {
            $day = date('l', strtotime($date));
            $thisWeek = ($date > date('Y-m-d') && $date < date('Y-m-d', strtotime('today + 6 days')));
            if ($thisWeek) {
                $day = 'This ' . $day;
            }
        }
        $timestamp = strtotime($date);

        $pattern = 'M j, Y';
        $headerShortDate = '<h2 class="short_date">' . date($pattern, $timestamp) . '</h2>';
        $headerDay = '<h2 class="day">' . $day . '</h2>';
        $headers = $headerShortDate . $headerDay;
        $classes = 'event_accordion';
        if ($thisWeek) {
            $classes .= ' thisWeek';
        }

        return "<hgroup class='$classes'>$headers</hgroup>";
    }

    /**
     * format time of event
     *
     * @param array $event which needs its time formatted
     * @return string
     */
    public function eventTime($event)
    {
        $startStamp = $event->time_start;
        if (substr($startStamp->i18nFormat(), -5, 2) == '00') {
            $retval = date('ga', strtotime($startStamp));
        } else {
            $retval = date('g:ia', strtotime($startStamp));
        }
        if ($event['time_end']) {
            $endStamp = $event->time_end;
            if (substr($endStamp->i18nFormat(), -5, 2) == '00') {
                $retval .= ' to ' . date('ga', strtotime($endStamp));
            }
        }

        return $retval;
    }

    /**
     * Returns a linked list of tags
     *
     * @param array $event for these tags
     * @return string
     */
    public function eventTags($event)
    {
        $linkedTags = [];
        foreach ($event['tags'] as $tag) {
            $tagLinkId = "filter_tag_inline_{$event['id']}_{$tag['id']}";
            $tagSlug = "{$tag['id']}_" . Inflector::slug($tag['name']);
            $linkedTags[] = $this->Html->link(
                $tag['name'],
                [
                    'controller' => 'events',
                    'action' => 'tag',
                    'slug' => $tagSlug
                ],
                [
                    'escape' => false,
                    'id' => $tagLinkId
                ]
            );
        }

        return implode(', ', $linkedTags);
    }

    /**
     * Returns a formatted version of the date of the provided event
     *
     * @param array $date needing formatted
     * @return string
     */
    public function date($date)
    {
        return date('l, F j, Y', strtotime($date));
    }

    /**
     * Returns a formatted version of the time of the provided event
     *
     * @param array $event which needs formatted
     * @return string
     */
    public function time($event)
    {
        $retval = date('g:ia', strtotime($event->time_start));
        if ($event->time_end) {
            $retval .= ' to ' . date('g:ia', strtotime($event->time_end));
        }

        return $retval;
    }

    /**
     * Returns a linked arrow to the previous day
     *
     * @param int $timestamp Of the previous day
     * @return string
     */
    public function prevDay($timestamp)
    {
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
     *
     * @param int $timestamp Of the next day
     * @return string
     */
    public function nextDay($timestamp)
    {
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
     * Returns a linked arrow to the previous month
     *
     * @param int $month of prev month
     * @param int $year of prev month
     * @return string
     */
    public function prevMonth($month, $year)
    {
        return $this->Html->link(
            '&larr; Previous Month',
            [
                'controller' => 'events',
                'action' => 'month',
                $month - 1,
                $year
            ],
            ['escape' => false]
        );
    }

    /**
     * Returns a linked arrow to the next month
     *
     * @param int $month of next month
     * @param int $year of next month
     * @return string
     */
    public function nextMonth($month, $year)
    {
        return $this->Html->link(
            'Next Month &rarr;',
            [
                'controller' => 'events',
                'action' => 'month',
                $month + 1,
                $year
            ],
            ['escape' => false]
        );
    }

    /**
     * Outputs either a thumbnail (square) image or a small (width-limited) image
     *
     * @param string $type 'small' or 'tiny'
     * @param array $params for the image
     * @return string
     */
    public function thumbnail($type, $params)
    {
        if (!isset($params['filename'])) {
            return '';
        }
        $filename = $params['filename'];
        $reducedPath = WWW_ROOT . 'img' . DS . 'events' . DS . $type . DS . $filename;
        if (!file_exists($reducedPath)) {
            return '';
        }
        $fullPath = WWW_ROOT . 'img' . DS . 'events' . DS . 'full' . DS . $filename;
        $class = "thumbnail tn_$type";
        if (isset($params['class'])) {
            $class .= ' ' . $params['class'];
        }

        // Reduced image
        $image = "<img src='/img/events/$type/$filename' class='$class'";

        if (!file_exists($fullPath)) {
            return $image;
        }

        // Link to full image
        $rel = "popup";
        if (isset($params['group'])) {
            $rel .= '[' . $params['group'] . ']';
        }
        $link = "a href='/img/events/full/$filename' rel='$rel' class='$class' alt='$filename'";
        if (isset($params['caption']) && !empty($params['caption'])) {
            $link .= ' title="' . $params['caption'] . '"';
        }
        $link .= ">$image</a>";

        return $link;
    }
}
