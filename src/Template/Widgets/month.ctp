<table class="calendar" id="calendar_<?= "$year-$month"; ?>" data-year="<?= $year; ?>" data-month="<?= $month; ?>">
     <thead>
          <tr>
               <td class="prev_month">
                    <a href="#" class="prev_month" title="Previous month">
                         &larr;
                    </a>
               </td>
               <th colspan="5" class="month_name">
                    <?= $monthName; ?>
               </th>
               <td class="next_month">
                    <a href="#" class="next_month" title="Next month">
                         &rarr;
                    </a>
               </td>
          </tr>
          <tr>
               <?php foreach (['S', 'M', 'T', 'W', 'T', 'F', 'S'] as $letter): ?>
                    <th class="day_header">
                         <?= $letter; ?>
                    </th>
               <?php endforeach; ?>
          </tr>
     </thead>
     <tbody>
          <?php
            for ($callNum = 0; $callNum <= 42; $callNum++) {

                // Beginning of row
                if ($callNum % 7 == 0) {
                    echo '<tr>';
                }

                // Pre-spacer
                if ($callNum < $preSpacer) {
                    echo '<td class="spacer">&nbsp;</td>';
                }

                // Calendar date
                if ($callNum >= $preSpacer && $callNum < $preSpacer + $lastDay) {
                    $day = $callNum - $preSpacer + 1;
                    echo ("$year$month$day" == $today) ? '<td class="today">' : '<td>';
                    echo '<div>';
                    //echo '<span class="number">'.$day.'</span>';

                    echo $this->Html->link(
                        $day,
                        [
                            'controller' => 'events',
                            'action' => 'day',
                            $month,
                            $day,
                            $year
                        ],
                        [
                            'class' => 'date',
                            'data-day' => str_pad($day, 2, '0', STR_PAD_LEFT)
                        ]
                    );

                    $date = $year.'-'.str_pad($month, 2, '0', STR_PAD_LEFT).'-'.str_pad($day, 2, '0', STR_PAD_LEFT);
                    if (isset($events[$date]) && !empty($events[$date])) {
                        echo '<ul>';
                        if (!isset($events[$date][0])) {
                            $event = $events[$date];
                        } else {
                            foreach ($events[$date] as $day => $dayEvent) {
                                $event = $dayEvent;
                            }
                        }
                        echo "<li>";

                            // Event link
                            $link_text = $this->Text->truncate(
                                $event->title,
                                50,
                                [
                                    'ending' => '...',
                                    'exact' => false
                                ]
                            );
                        $category_name = $event->category->name;
                        $link_text = $this->Icon->category($category_name).$link_text;
                        echo $this->Html->link(
                                $link_text,
                                [
                                    'controller' => 'events',
                                    'action' => 'view',
                                    'id' => $event->id
                                ],
                                [
                                    'escape' => false,
                                    'class' => 'event',
                                    'data-event-id' => $event->id,
                                    'title' => $event->displayed_time.' - '.$event->title
                                ]
                            );

                        echo '</li>';
                    }
                    echo '</ul>';
    /*                $count = count($events[$date]);
                    if ($eventsDisplayedPerDay > 0 && $count > $eventsDisplayedPerDay) {
                        echo $this->Html->link(
                                $count - $eventsDisplayedPerDay.' more',
                                [
                                    'controller' => 'events',
                                    'action' => 'day',
                                    $month,
                                    $day,
                                    $year
                                ],
                                [
                                    'class' => 'more',
                                    'data-day' => str_pad($day, 2, '0', STR_PAD_LEFT),
                                    'title' => 'View all events on this date'
                                ]
                            );
                    } */
                }
                echo '</div></td>';
            }

                // After the last day
                if ($callNum >= $preSpacer + $lastDay - 1) {

                    // End of calendar
                    if ($callNum % 7 == 6) {
                        echo '</tr>';
                    #    break;

                    // Normal spacer
                    } else {
                        echo '<td class="spacer">&nbsp;</td>';
                    }
                }

                // End of row
                if ($callNum % 7 == 6) {
                    echo '</tr>';
                }
            #}
        ?>
     </tbody>
</table>

<?php $this->Js->buffer("
     muncieEventsMonthWidget.setCurrentMonth('$month');
     muncieEventsMonthWidget.setCurrentYear('$year');
     muncieEventsMonthWidget.prepareLinks('#calendar_$year-$month');
     var events = ".$this->Js->object($eventsForJson).";
     muncieEventsMonthWidget.setEvents(events);
"); ?>
